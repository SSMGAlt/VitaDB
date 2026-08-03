# VitaDB (community reconstruction)

This is a fork of VitaDB, a database of PS Vita homebrews, plugins, and PC
tools originally created and run by Rinnegatamante. The original site was
shut down by its creator in August 2026.

This repository is not the original project. It is not run by, endorsed
by, or affiliated with Rinnegatamante in any way. It exists because the
original database and its listing of community homebrews would otherwise
have disappeared entirely. Full credit for the original idea, design, and
years of work goes to Rinnegatamante and everyone who contributed content
to the original site.

This is also not meant to be a general purpose database platform. It is
specifically a reconstruction of VitaDB, built to keep the existing PS
Vita and PSP homebrew community's work discoverable and downloadable.

## What's here

- **Homebrews, Plugins, PC Tools, and PSP Homebrews**, each with their own
  listing page, submission form, and JSON/YAML API endpoint
- A **Developer API** page documenting the public endpoints (the PSP
  Homebrew endpoints exist and work the same way, they're just not listed
  there yet)
- **Titles List**, mapping PS Vita title IDs to known homebrews
- **Staff List** and **Supporters** pages
- **Top 50 Developers**, ranking contributors by how many game ports
  they've made
- A **Bounties** link, pointing to an external bounty board rather than
  hosting one in-app

## Tech stack

Plain PHP (mysqli, no framework) on the backend, AngularJS 1.5 on the
frontend, MariaDB for storage. No build step, no bundler. Dependencies are
either vendored directly in the repo or loaded from a CDN.

## Deploying this yourself

Deployment is Docker Compose based, sitting behind a Cloudflare Tunnel,
with a GitHub webhook that auto-deploys on push. Everything needed to
stand up your own copy lives in the `deploy/` folder, and the full
step by step walkthrough (server setup, Cloudflare Tunnel, the webhook,
troubleshooting) is its own separate guide, distributed alongside this
repo rather than inside it.

Quick summary if you already know your way around Docker: clone this
repo, create `config.php` and an env file one directory above the
checkout (see `deploy/.env.example`), then run `deploy/update.sh`. That
same command is also what the auto-deploy webhook runs on every push.

## Contributing

Bug reports and feature requests use the issue templates under
`.github/ISSUE_TEMPLATE/`. Pull requests run through a linting check
(PHP syntax plus ESLint on the frontend JS) and CodeQL scanning on the
JavaScript side. CodeQL doesn't support PHP, so backend changes aren't
covered by static analysis here yet.

## License

GNU General Public License v3.0. See `LICENSE`.
