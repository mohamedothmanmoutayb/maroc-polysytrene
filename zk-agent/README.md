# ZKTeco attendance agent

Runs on a machine **inside the office LAN** — the same network as the ZKTeco
device (`192.168.1.13` by default). It reads punches from the device and
pushes them to the hosted app over HTTPS. It does not run on the web server;
the web server has no network route to the office LAN (confirmed: no VPN/TUN
capability and no port-forward set up on that side).

## Setup

1. Copy this entire `zk-agent/` folder to a PC on the office network that's
   left on and connected (a small office server, NAS with PHP, or an
   always-on workstation).
2. Install PHP (>= 8.1) and Composer on that machine if not already present.
3. From inside `zk-agent/`, run:
   ```
   composer install
   ```
4. Copy `.env.example` to `.env` and fill in:
   - `ZK_DEVICE_IP` — the device's LAN IP (default `192.168.1.13`)
   - `ZK_AGENT_API_KEY` — the key generated on the server, currently:
     `7ec127c8c70270eaabf1803071f81d3e7bc67a8456eda04ffa1a4d4fa177fad4`
     (this must match `ZK_AGENT_API_KEY` in the hosted app's `.env` — if you
     ever rotate it, update both sides)
5. In the app itself, open each employee's edit page and set their
   **"ID Pointeuse (ZKTeco)"** field to that employee's numeric user ID on
   the device. Punches for employees without this set are logged and
   skipped, not lost — set it any time and re-run the sync.
6. Test it manually first:
   ```
   php sync.php
   ```
   It should print how many punches it read and confirm the push succeeded.
   Check the app's attendance calendar for that employee/day afterward.
7. Once that works, schedule it to run automatically:
   - **Linux/macOS (cron)**: `crontab -e`, add:
     ```
     */15 * * * * /usr/bin/php /path/to/zk-agent/sync.php >> /path/to/zk-agent/sync.log 2>&1
     ```
   - **Windows (Task Scheduler)**: create a task that runs
     `php.exe C:\path\to\zk-agent\sync.php` every 15 minutes.

## How it works

- The device's own attendance log is never cleared — it stays the source of
  truth on the device. Every run re-reads the full log.
- The server dedupes by `(employee, timestamp)`, so re-sending punches it
  has already seen is safe and cheap — nothing is duplicated.
- Each employee's day is rebuilt from all of that day's punches, paired
  sequentially: 1st = check-in, 2nd = check-out, 3rd = check-in, etc.
