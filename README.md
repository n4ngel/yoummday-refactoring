# Nikola's notes for the refactoring task
## The guidelines I followed during my work
Considering the current state of the endpoint to be refactored, I was guided by the principle of not breaking the existing (expected) behavior and that all additional features could be used as desirable but optional, thus refactoring the code without introducing any breaking changes.
- The current state implies a single permission check (read). The endpoint behaves unchanged if no additional query parameters are present. If a query parameter is present, it is validated and the permission checked based on its value.
- Given that the existing permission values are specified in the `TokenDataProvider` class, it is assumed that these are also the allowed values (_read_ and _write_), which is reflected by the values in the `Permission` enum class.
- A new, optional `error` resource has been added to the response. The goal is to add more context to failure cases. Clients may ignore it if they don't use it. 
  - If in some case, the clients enforce strict schema matching, then this resource would represent a breaking change and its introduction would be beyond the scope of this task.
- `Fig\Http\Message\StatusCodeInterface` is used as centralized source for the response status codes, considering that it's already used as source of common HTTP status codes in the framework

#### API Request Examples:
1. `GET /has_permission?permission=read` → Will check for `read` permission.
2. `GET /has_permission?permission=write` → Will check for `write` permission.
3. `GET /has_permission` (no query param) → Defaults to `read` permission.
4. `GET /has_permission?permission=delete` → Returns `400 Bad Request`.


# ❗ Please do not fork this repository ❗

# Yoummday Refactoring Task
This project only includes the route `GET /has_permission/{token}` which has to decide if the provided token exists and has the required permission.
Your task is to refactor the endpoint and create tests, if necessary.

# Requirements
- php 8.1
- composer

# Installation
```shell
$ composer install
```

# Run
```shell 
$ php src/main.php
```
Expected output: 
```shell
[INFO] Registering GET /has_permission/{token}
[INFO] Server running on 127.0.0.1:1337
```

# Testing
```shell
$ php vendor/bin/phpunit Test
```
