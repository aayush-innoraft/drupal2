Content Language Access Module restricts the access of only contents with
language (except neutral language) that are equal of the actual Drupal
language being accessed or others that were previous configured in the admin
page.

This module helps when you have a content that needs to have access restriction
by Drupal language.

This module detects the content language based on the settings defined on
Config -> Languages -> Detection and selection

Example
------------
  Domains:
    www.example.com (EN_US)
    www.example.com.br (PT_BR)

  Contents:
    node/20 (EN_US)
    node/21 (PT_BR)
    node/22 (Language Neutral)

  Results:
    www.example.com/node/20 - response: 200 - OK
    www.example.com.br/node/20 - response: 403 - Access Denied

    www.example.com/node/21 - response: 403 - Access Denied
    www.example.com.br/node/21 - response: 200 - OK

    www.example.com/node/22 - response: 200 - OK
    www.example.com.br/node/22 - response: 200 - OK

Installation
------------
Just enable it. No initial configuration required.

Configuration
------------
Additional settings (for example, to allow content from an specified list of
languages to be accessed from a language detected) can be set on Config ->
Content language access
