# Changes

## 0.1

- Added a WordPress settings page under `Settings > llms.txt` with a large textarea for managing the `llms.txt` contents from the admin UI.
- Added root-level `llms.txt` output that serves the saved content as plain text for bots and other clients requesting `/llms.txt`.
- Kept the frontend response intentionally minimal and safer by limiting it to `GET` and `HEAD`, returning `text/plain`, and sending `X-Content-Type-Options: nosniff`.
- Added GitHub-based plugin update support using `plugin-update-checker`, pointed at `https://github.com/jonschr/elodin-llms-editor` on the `master` branch.
