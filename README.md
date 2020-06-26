# Tatter\Pushover
Pushover integration for CodeIgniter 4

[![](https://github.com/tattersoftware/codeigniter4-pushover/workflows/PHP%20Unit%20Tests/badge.svg)](https://github.com/tattersoftware/codeigniter4-pushover/actions?query=workflow%3A%22PHP+Unit+Tests)

## Quick Start

1. Install with Composer: `> composer require --dev tatter/pushover`
2. Send an alert: `\Tatter\Pushover\Pushover::message()`

## Description

**Tatter\Pushover** adds an easy-to-use Service wrapper for [Pushover](https://pushover.net)
to your CodeIgniter 4 project.

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**examples/Pushover.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** then the library will use its own.

## Usage
