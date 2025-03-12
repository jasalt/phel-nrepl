# Phel-nrepl (WIP)

`Status: work in progress, some initial wiring done`

Phel [Phel](https://phel-lang.org/) nREPL server implementation leveraging [amphp/socket](https://amphp.org/socket) for async processing.
Uses https://gitlab.com/jasalt/phel-bencode for bencode conversion.

## Usage

Start server:
```
composer install
vendor/bin/phel run src/nrepl.phel
```

Test communication with `nc`:
```
$ nc localhost 8888
nREPL server started on port 8888 on host 127.0.0.1 - nrepl://127.0.0.1:8888
d2:op8:describee
d3:opsl4:eval5:clone8:describee6:statusl4:donee8:versionsd4:phel8:dev-main3:php6:8.2.26ee
d4:code7:(+ 1 1)2:op4:evale
d6:statusl4:donee5:valuei2ee
```

## Resources
Some helpful tutorials:

- https://nrepl.org/nrepl/building_servers.html
- https://blog.djy.io/alda-and-the-nrepl-protocol/
- https://mauricio.szabo.link/blog/2020/04/04/implementing-a-nrepl-client/

Nrepl server implementations:
- https://github.com/ikappaki/basilisp-nrepl-async
- https://github.com/babashka/nbb/blob/83e2684e86319543aca815a9a7c373d0de7fd487/src/nbb/impl/nrepl_server.cljs

Useful Phel functions in ILT project: https://codeberg.org/mmontone/interactive-lang-tools/src/branch/master/backends/phel/ilt.phel

## Later (?)
  Stream processing could be utilized if it brings benefit,

  There's support in PHP bencode library that might work:
  https://sandfox.dev/php/bencode/decoding.html#working-with-streams

  Amphp Byte Stream Json decoder example:
  https://github.com/amphp/byte-stream/blob/daa00f2efdbd71565bf64ffefa89e37542addf93/src/functions.php#L168

## License

Licensed under the [MIT license](https://opensource.org/licenses/MIT).
