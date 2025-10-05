Update README files (English & Russian) with top badges, contact table, and footer.

Usage:
  php bin/update-readme.php

CI integration (composer.json):
{
  "scripts": {
    "update-readme": "php bin/update-readme.php"
  }
}

GitHub Actions step:
  - name: Update README files
    run: composer run update-readme

Environment overrides (optional):
  GITHUB_REPOSITORY=taa0662621456/object-foundation
  COMPOSER_PACKAGE=marketingamericacorp/object-foundation
  ORG_PHONE="+1 707-867-5833"
  ORG_EMAIL="taa0662621456@gmail.com"
