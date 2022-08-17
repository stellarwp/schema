# Automated testing

This repository uses Codeception for automated testing and is configured to use [`tric`](https://github.com/the-events-calendar/tric) to run them.

## Getting `tric`

Clone [`tric`](https://github.com/the-events-calendar/tric) somewhere on your machine and ensure it is in your path.

```bash
cd ~/git/
git clone git@github.com:the-events-calendar/tric.git
```

## Tell `tric` where to look for "plugins" to test

```bash
cd ~/git

tric here
```

## Tell `tric` to use `schema` as the plugin to test

```bash
tric use schema
```

## Run the tests

```bash
tric run wpunit
```

Or go into `tric shell` (this is faster) and run the tests:

```bash
tric shell

# Your prompt will look like this: ".../plugins/schema > "
# Run the following command to run the wpunit tests:
cr wpunit

# To leave tric shell:
exit
```
