PHPUNIT="vendor/bin/phpunit"
PORT=8000

testing: ; $(PHPUNIT) --testdox
coverage: ; $(PHPUNIT) --coverage-html coverage-report
server: ; php -S localhost:$(PORT) -t coverage-report
