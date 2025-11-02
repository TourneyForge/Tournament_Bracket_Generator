## Installation & updates

Clone the repository
`git clone https://github.com/TourneyForge/Tournament_Bracket_Generator.git` then `composer update`

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Rename `env` to `.env` and tailor for your app, specifically the baseURL and any database settings.

or

Set the baseURL in app/Config/App.php file and any database settings in app/Config/Database.php file.

## Server Requirements

OS: Linux/Windows

CPU: 1 vCore

RAM: 1 GB

Storage: 40 GB

Bandwidth: 100 Mbps unmetered

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> The end of life date for PHP 7.4 was November 28, 2022.
> The end of life date for PHP 8.0 was November 26, 2023.
> If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> The end of life date for PHP 8.1 will be November 25, 2024.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

[Supported Databases](https://codeigniter.com/user_guide/intro/requirements.html#supported-databases)

A database is required for most web application programming. Currently supported databases are:

- MySQL via the MySQLi driver (version 5.1 and above only)

- PostgreSQL via the Postgre driver (version 7.4 and above only)

- SQLite3 via the SQLite3 driver

- Microsoft SQL Server via the SQLSRV driver (version 2012 and above only)

- Oracle Database via the OCI8 driver (version 12.1 and above only)

Not all of the drivers have been converted/rewritten for CodeIgniter4. The list below shows the outstanding ones.

- MySQL (5.1+) via the pdo driver

- Oracle via the pdo drivers

- PostgreSQL via the pdo driver

- MSSQL via the pdo driver

- SQLite via the sqlite (version 2) and pdo drivers

- CUBRID via the cubrid and pdo drivers

- Interbase/Firebird via the ibase and pdo drivers

- ODBC via the odbc and pdo drivers (you should know that ODBC is actually an abstraction layer)

## Shield Authentication enable

Shield is the official authentication and authorization framework for CodeIgniter 4. While it provides a base set of tools commonly used in websites, it is designed to be flexible and easily customizable.

The primary goals for Shield are:

- It must be very flexible and allow developers to extend/override almost any part of it.
- It must have security at its core. It is an auth lib after all.
- To cover many auth needs right out of the box, but be simple to add additional functionality to.

To install Shield auth, run the following command.

`php spark shield:setup`

When prompt a questions to overwrite the existing configurations, select `'n'`.

On the final prompt `Run `spark migrate --all`now? [y, n]:`, select `'y'`.

## Google authentication

1. Set Up Google API Credentials
   First, you need to create a project in the Google Cloud Console and obtain OAuth 2.0 credentials.

- Go to the [Google Cloud Console](https://console.cloud.google.com/).
- Create a new project or select an existing one.
- Navigate to "Credentials" in the sidebar.
- Create credentials > OAuth 2.0 Client ID.
- Configure the consent screen, including adding authorized redirect URIs (e.g., http://localhost:8080/callback).
- Create the client ID and download the client_secret.json file or copy the Client ID and Client Secret.
- Add the credentials into the .env file

  > `GOOGLE_CLIENT_ID = {google_client_id}`

  > `GOOGLE_CLIENT_SECRET = {google_client_secret}`

  > `GOOGLE_REDIRECT_URI = http://localhost:8080/auth/google/callback`

2. Install Google Client Library

- To interact with Google's OAuth 2.0 API, you need to install the Google API PHP client library. You can do this using Composer:
  `composer require google/apiclient:^2.0`

## Create the upload directory

- Create the Uploads Directory
  `mkdir -p /path/to/your/codeigniter/writable/uploads`
- Create the Symbolic Link

  On Windows

  > `mklink /D \"C:\path\to\your\codeigniter\public\uploads\" \"C:\path\to\your\codeigniter\writable\uploads\"`

  On Linux

  > `ln -s /path/to/your/codeigniter/writable/uploads /path/to/your/codeigniter/public/uploads`

- You can change the directory structure by editing the Upload config file(`App/Config/UploadConfig.php`).
- Upload directory structure

  > Directory for the local audio files uploaded: `public/uploads/audios/local/`

  > Directory for the youtube audio files uploaded: `public/uploads/audios/url/`

  > Directory for the local video files uploaded: `public/uploads/videos/local/`

  > Directory for the youtube video files uploaded: `public/uploads/videos/url/`

  > Directory for the participant images uploaded: `public/uploads/images/participants/`

  > Directory for the csv files uploaded: `public/uploads/CSV/UserLocal/`

## Start Websocket server

Start a websocket server to update tournament progress on the bracket page in real time.

To run websocket server, run the following command.

`php spark socket:start`

If you want to change the port to custom one, update the `ws.php` file in project root directory.

## Set Up Windows Task Scheduler

If the tournament availability is enabled, you will need to start/complete the tournament and update the rounds on the scheduled time.

To do this actions, you can schedule the tasks by using Task Scheduler app on Windows or set the cron job on Linux system.

These will run the command to check if there is the scheduled tasks from the database to start, update, or complete the tournaments and rounds.

For example, if you set the trigger to 15 minutes, it will run the specified command on every 15 minutes.

On Windows

1. Open Task Scheduler:

   Press `Windows Key + R` and type `taskschd.msc` to open Task Scheduler.

2. Create a New Task:

   In Task Scheduler, click `Create Task` on the right.

3. General Tab:

   - Name the task something like "Tournaments Scheduled Task".

   - Select "Run whether user is logged on or not" (optional).

   - Check the box for "Run with highest privileges" (optional, but useful for permission issues).

4. Triggers Tab:

   - Add a trigger by clicking `New`.

   - Set the trigger to run daily, weekly, or as per your scheduling needs.

     > On Settings part, select `Daily`

     > On Advanced settings, check `Repeat task every:` 15 minutes, and `for a duration of:` 1 day

5. Actions Tab:

   - Add a new action by clicking `New`.

   - Choose `Start a program`.

   - In the `Program/script` field, point to your PHP executable, typically located at `C:\path\to\php\php.exe`.

   - In the `Add arguments` field, specify the CodeIgniter controller or CLI command:

     > `C:\path\to\your\project\spark task:run`

6. Conditions and Settings:

   - In the `Conditions` tab, uncheck the option "Start the task only if the computer is on AC power" if needed.

   - In the `Settings` tab, configure how the task behaves (e.g., whether to stop it after a certain time, etc.).

7. Save the Task:

> Save the task and enter your system password if prompted.

On Linux

1. Open the Crontab Editor:

> `crontab -e`

2. Add a New Cron Job:

> `* * * * * /usr/bin/php /path/to/your/project/spark task:run`

3. Save the Crontab File: After adding the cron job, save and close the crontab file. The task is now scheduled to run at the specified interval.

## Do I need any other programs?

For URL audio/video generation feature, youtube is used as the source and thus requires installing [FFMpeg](https://www.ffmpeg.org/) and [yt-dlp](https://github.com/yt-dlp/yt-dlp).

By updated Youtube API policy, it's required to signin to extract the video or audio. But Youtube API doesn't support the user authentication in terminal.
Therefore we must use cookie authentication through terminal. To use cookie authentication, navigate to browser on server and login to youtube.com.
Then Export the cookie file as follows.

1. You may also use a conforming browser extension for exporting cookies, such as [Get cookies.txt LOCALLY](https://chrome.google.com/webstore/detail/get-cookiestxt-locally/cclelndahbckbenkjhflpdbgdldlbecc) for Chrome or [cookies.txt](https://addons.mozilla.org/en-US/firefox/addon/cookies-txt/) for Firefox. As with any browser extension, be careful about what you install. If you had previously installed the "Get cookies.txt" (not "LOCALLY") Chrome extension, it's recommended to uninstall it immediately; it has been reported as malware and removed from the Chrome Web Store.
2. After that, upload cookie file under the directory FFmpeg was installed and replace existing/old one. (for example, C:\ffmpeg\www.youtube.com_cookies.txt)

Note that this export step is required in case youtube account is signed out and requires re-login (may happen every once a while)

To update this program to the latest version, you can navigate the the directory which the yt-dlp is installed and then run the following command.

> `cd .\bin`

> `yt-dlp -U`

## Email configuration

Set the configuration variables in `App\Config\Email.php` or `.env` file.

If you send the emails through the smtp protocol, set the following variables in the .

> `public string $fromEmail = "info@tourneyforge.com"`

> `public string $fromName = "Tourney Forge"`

> `public string $protocol = 'smtp'`

> `public string $SMTPHost = '[hostname]'`

> `public string $SMTPUser = '[username]'`

> `public string $SMTPPass = '[password]'`

> `public int $SMTPPort = 25`

or (.env file)

> `Email.fromEmail = "info@tourneyforge.com"`

> `Email.fromName = "Tourney Forge"`

> `Email.protocol = 'smtp'`

> `Email.SMTPHost = '[hostname]'`

> `Email.SMTPUser = your_email@example.com`

> `Email.SMTPPass = your_password`

> `Email.SMTPPort = 25`

If you use the SendGrid API to send the emails,

(in App\Config\Email.php)

> `public string $fromEmail = "info@tourneyforge.com"`

> `public string $fromName = "Tourney Forge"`

> `public string $protocol = sendgrid`

> `public string $SENDGRID_API_KEY = [SendGrid API Key]`

(in .env)

> `Email.fromEmail = "info@tourneyforge.com"`

> `Email.fromName = "Tourney Forge"`

> `Email.protocol = sendgrid`

> `SENDGRID_API_KEY = [SendGrid API Key]`
