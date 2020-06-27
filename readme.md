# Component BackupFile
Component that allows you to send an email to save files from the server (database dump included).

## Prerequisite

* PHP 7.4+
* [SMTP or 3rd party provider](https://symfony.com/doc/current/mailer.html#transport-setup)
* PHP extension
    * _(optional)_ Pdo, pdo-mysqli or another database (sqlite, etc)

## Install
Edit your [composer.json](https://getcomposer.org) (launch `composer update` after edit):
```json
{
  "repositories": [
    { "type": "git", "url": "git@github.com:jgauthi/component_backup_file.git" }
  ],
  "require": {
    "jgauthi/component_backup_file": "1.*"
  }
}
```


## Documentation
You can look at [folder example](https://github.com/jgauthi/component_backup_file/tree/master/example).

