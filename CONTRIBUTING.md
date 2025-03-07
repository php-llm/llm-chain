# Contributing

You want to contribute to this project? Great! We are happy to welcome you to the repository. Please read the following
guidelines to get started. It will help to make the contribution process easy and effective for everyone involved.

## Disclaimer

Before you start contributing, please be aware that this project is not commercial and is maintained by volunteers in
their free time to share code among themselves and with the community. We are happy to invest our time in this project
and to share it with you. However, we cannot guarantee that we will be able to respond to your requests in a timely
manner or that we will be able to implement your suggestions.

## Questions and Support

If you have any questions or problems while using LLM Chain, please check the [README](README.md) file and also the
[examples](examples) folder. If you can't find the answer there, feel free to open an
[issue](https://github.com/php-llm/llm-chain/issues).

## Bug Reports 

If you open an issue to report a bug, please make sure to provide enough information to reproduce the bug. Ideally even
provide a code snippet that reproduces the bug. This will help us to fix the bug faster.

## Feature Ideas

Of course, we are happy to get your ideas for new features. And for sure, we are happy if you want to implement them.
However, to make sure that you are not wasting your time, please open an issue first to discuss your idea in case it is
a larger change.

## Pull Requests

When you end up implementing a new feature or fixing a bug, please open a pull request. The pipeline will help you to
check if your code is following the coding standards and if all tests are passing. To execute the tools locally, you
can use the following commands:

```bash
make ci-stable # execute all checks with stable dependencies
make ci-lowest # execute all checks with lowest dependencies
``` 

Commit messages should follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification.
