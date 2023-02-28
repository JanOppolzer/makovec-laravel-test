# Makovec-Laravel

Makovec is a simple [Laravel](https://www.laravel.com)-based web application to manage devices not using 802.1X to connect to network in Telehouse.

[![Actions Status](https://github.com/JanOppolzer/makovec-laravel-test/workflows/Laravel/badge.svg)](https://github.com/JanOppolzer/makovec-laravel-test/actions)

## Requirements

This application is written in Laravel 10 and uses PHP version at least 8.1.

Authentication is managed by locally running Shibboleth Service Provider, so Apache web server is highly recommended as there is an official Shibboleth module for Apache.

- PHP 8.1
- MariaDB 10
- Shibboleth SP 3
- Apache 2.4

The above mentioned requirements can easily be met by using Ubuntu 22.04 LTS (Jammy Jellyfish). For those running older Ubuntu or Debian, [Ondřej Surý's PPA repository](https://launchpad.net/~ondrej/+archive/ubuntu/php/) might be very appreciated.

## Installation

The easiest way to install Makovec is to use [Envoy](https://laravel.com/docs/10.x/envoy) script in [makovec-envoy](https://github.com/JanOppolzer/makovec-envoy) repository. That repository also contains configuration snippets for Apache and Shibboleth SP.

To prepare the server for Makovec, I am using an [Ansible](https://www.ansible.com) playbook that is currently not publicly available due to being part of our larger and internal mechanism, but I am willing to share it and most probably will do that in the future.
