# Contributing to Laravel Artifacts

Thank you for considering contributing to Laravel Artifacts! We welcome contributions from everyone.

## Development Setup

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR-USERNAME/laravel-artifacts.git`
3. Install dependencies: `composer install`
4. Create a new branch: `git checkout -b feature/your-feature-name`

## Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse

# Format code
composer format
```

## Code Style

We use Laravel Pint for code formatting. Before submitting a PR, please run:

```bash
composer format
```

## Pull Request Process

1. Ensure all tests pass
2. Update the README.md with details of changes if applicable
3. Update the CHANGELOG.md with a note describing your changes
4. Make sure your code follows PSR-12 coding standards
5. Write clear, descriptive commit messages
6. Open a pull request with a clear title and description

## Commit Message Guidelines

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

Example:
```
Add merge conflict detection

- Implement semantic diff algorithm
- Add conflict resolution UI
- Update tests for merge service

Fixes #123
```

## Testing Guidelines

- Write tests for all new features
- Ensure backward compatibility
- Aim for >85% code coverage
- Use descriptive test method names

## Reporting Bugs

When reporting bugs, please include:

1. Your PHP and Laravel version
2. Steps to reproduce the bug
3. Expected behavior
4. Actual behavior
5. Any error messages or stack traces

## Feature Requests

We love feature requests! Please:

1. Check if the feature has already been requested
2. Clearly describe the feature and its use case
3. Explain why this feature would be useful to most users

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for all.

### Our Standards

- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

## Questions?

Feel free to open an issue or reach out to the maintainers.

Thank you for contributing! 🎉
