#!/usr/bin/env python3
"""
Minimal GitHub webhook listener for VitaDB auto-deploy.

Listens on 127.0.0.1:9010 (localhost only - reached via Cloudflare Tunnel,
never exposed directly), verifies GitHub's HMAC-SHA256 signature on every
request, and on a push to the configured branch runs deploy/update.sh.

Standard library only - nothing to pip install.
"""
import hashlib
import hmac
import json
import logging
import os
import subprocess
import sys
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

REPO_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))  # .../VitaDB
UPDATE_SCRIPT = os.path.join(REPO_DIR, "deploy", "update.sh")

SECRET = os.environ.get("WEBHOOK_SECRET", "")
BRANCH = os.environ.get("DEPLOY_BRANCH", "master")
HOST = os.environ.get("WEBHOOK_HOST", "127.0.0.1")
PORT = int(os.environ.get("WEBHOOK_PORT", "9010"))
LOG_FILE = os.environ.get("WEBHOOK_LOG", os.path.join(os.path.dirname(REPO_DIR), "webhook.log"))

logging.basicConfig(
    filename=LOG_FILE,
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
)
log = logging.getLogger("vitadb-webhook")


def verify_signature(body: bytes, signature_header: str) -> bool:
    if not SECRET:
        log.warning("WEBHOOK_SECRET is not set - refusing all requests")
        return False
    if not signature_header or not signature_header.startswith("sha256="):
        return False
    expected = "sha256=" + hmac.new(SECRET.encode(), body, hashlib.sha256).hexdigest()
    return hmac.compare_digest(expected, signature_header)


class Handler(BaseHTTPRequestHandler):
    def log_message(self, fmt, *args):
        log.info("%s - %s", self.address_string(), fmt % args)

    def _respond(self, code: int, body: bytes):
        self.send_response(code)
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        # Simple healthcheck - handy for `curl` and for the Cloudflare route.
        self._respond(200, b"vitadb-webhook: ok")

    def do_POST(self):
        length = int(self.headers.get("Content-Length", 0))
        body = self.rfile.read(length) if length else b""
        signature = self.headers.get("X-Hub-Signature-256", "")

        if not verify_signature(body, signature):
            log.warning("Rejected webhook: missing or invalid signature")
            self._respond(401, b"invalid signature")
            return

        event = self.headers.get("X-GitHub-Event", "")

        if event == "ping":
            log.info("Received GitHub ping event")
            self._respond(200, b"pong")
            return

        if event != "push":
            log.info("Ignoring event type: %s", event)
            self._respond(200, b"ignored")
            return

        try:
            payload = json.loads(body or b"{}")
        except json.JSONDecodeError:
            payload = {}

        ref = payload.get("ref", "")
        if ref != f"refs/heads/{BRANCH}":
            log.info("Ignoring push to %s (watching %s)", ref, BRANCH)
            self._respond(200, b"ignored branch")
            return

        log.info("Push to %s received - starting deploy", BRANCH)
        self._respond(202, b"deploy started")

        try:
            result = subprocess.run(
                ["/usr/bin/env", "bash", UPDATE_SCRIPT],
                cwd=REPO_DIR,
                capture_output=True,
                text=True,
                timeout=600,
            )
            log.info("update.sh exit code: %s", result.returncode)
            if result.stdout:
                log.info("stdout:\n%s", result.stdout)
            if result.stderr:
                log.info("stderr:\n%s", result.stderr)
        except Exception:
            log.exception("Deploy failed")


def main():
    if not SECRET:
        print(
            "WARNING: WEBHOOK_SECRET is not set. All webhook requests will be rejected.",
            file=sys.stderr,
        )
    server = ThreadingHTTPServer((HOST, PORT), Handler)
    log.info("vitadb-webhook listening on %s:%s (branch=%s)", HOST, PORT, BRANCH)
    server.serve_forever()


if __name__ == "__main__":
    main()
