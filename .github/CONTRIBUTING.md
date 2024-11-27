# Contributing

Contributions are welcome and will be fully credited.

We accept contributions via Pull Requests on [GitHub](https://github.com/staudenmeir/eloquent-has-many-deep).

## Pull Requests

- **[PSR-12 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).

- **Add tests** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org). Randomly breaking public APIs is not an option.

- **Create feature branches** - Don't ask us to pull from your main branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Running Tests & Static Analysis

```
docker compose run --rm php8.4 composer install
docker compose run --rm php8.4 vendor/bin/phpunit
docker compose run --rm php8.4 vendor/bin/phpstan analyse --memory-limit=-1
docker compose run --rm php8.4 vendor/bin/phpstan analyse --configuration=phpstan.types.neon.dist --memory-limit=-1
```
