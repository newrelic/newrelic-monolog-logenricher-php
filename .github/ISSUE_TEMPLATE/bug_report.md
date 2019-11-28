---
name: Bug report
about: Describe a scenario in which this project behaves unexpectedly
title: ''
labels: bug, needs-triage
assignees: ''

---

[NOTE]: # ( ^^ Provide a general summary of the issue in the title above. ^^ )

## Description

[NOTE]: # ( Describe the problem you're encountering. )
[TIP]:  # ( Do NOT give us access or passwords to your New Relic account or API keys! )

## Steps to Reproduce

[NOTE]: # ( Please be as specific as possible. )

## Expected Behaviour

[NOTE]: # ( Tell us what you expected to happen. )

## Relevant Logs / Code Samples

[NOTE]: # ( Please provide any error logs or code samples if appropriate and possible. )

## Your Environment

[TIP]:  # ( Include as many relevant details about your environment as possible. )

* ex: PHP version
* ex: Operating system (and distribution, if relevant)
* ex: Monolog version
* ex: Monolog configuration (which handlers and processors are in use)

You can run this command in your project to quickly gather the version and OS
information:

```sh
php -v | head -n1; echo; composer show | grep 'monolog'; echo; uname -a; echo; [ -f /etc/lsb-release ] && cat /etc/lsb-release
```

## Additional context

[TIP]:  # ( Add any other context about the problem here. )
