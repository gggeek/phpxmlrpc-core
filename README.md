HTTPRPC for PHP - Core
======================

A php library for building http-based RPC clients and servers.

This is the core set of classes and interfaces, which is used by different packages implementing the various protocols:
- XML-RPC
- JSON-RPC
- SOAP
- REST

It currently relies on the phpxmlrpc/http package to implement the HTTP transport layer, with the goal of making that a
swappable component at some point in the future.

License
-------
Use of this software is subject to the terms in the [license.txt](license.txt) file
