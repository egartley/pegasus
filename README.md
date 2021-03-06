# Pegasus

Lightweight, simpler alternative to Wikipedia

## Status

Demo video: https://youtu.be/cBuraKz62Mg

Currently, you can create, edit and delete pages with content that includes different sections. Bold and italic text, as well as links are also supported. Changing the permalink structure (where pages can be accessed) and each page's slug is implemented.

Focusing on functionality and modularity, rather than making everything look nice (that comes later). Due to the use of HTML5, mainly [`contenteditable`](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/contenteditable), everything works best in a Chromium-based browser (Chrome, Edge, Yandex, Brave, Opera, or Vivaldi).

## Development

[XAMPP](https://www.apachefriends.org) for running a selfhosted Apache server, and JetBrains' [PhpStorm](https://www.jetbrains.com/phpstorm/) for writing the code, both PHP and JavaScript.

Note: The batch file `copy-peg.bat` included is not required, and therefore can be ignored. It is only for copying files from the XAMPP directory to the GitHub directory.

## Goals

- Create, edit and view pages
  - These may include text, images, graphs, and other media
- Maintain certain elements of Wikipedia
  - General look and feel
  - Basic functionality
- Ease of use for editing and viewing
- Be able to import pages from Wikipedia
- Dark and light themes (currently only dark)
- Code base that is easy to follow and modify
- "It just works"
