# GDPR Compliance - Automated Data Cleanup

This directory contains scripts for automatic data cleanup to ensure GDPR compliance within the QR Transfer application.

## Available Scripts

- `cleanup_old_events.php`: Automatically deletes events (and their associated data) that haven't been updated for more than one month.

## Setting up the Cron Job

To ensure regular execution of the cleanup process, set up a cron job on your server:

1. SSH into your server
2. Edit your crontab:
   ```
   crontab -e
   ```
3. Add a line to run the script daily at 3:15 AM (or your preferred time):
   ```
   15 3 * * * php /path/to/qrtransfer/cron/cleanup_old_events.php
   ```
4. Save and exit

## Logs

Execution logs for the GDPR cleanup process are stored in:
```
/path/to/qrtransfer/logs/gdpr_cleanup.log
```

## Manual Execution

To manually execute the cleanup process:

```bash
php /path/to/qrtransfer/cron/cleanup_old_events.php
```

The script will output a JSON response with details about the cleanup operation.
