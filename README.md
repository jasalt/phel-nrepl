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
Some helpful resources:

- https://nrepl.org/nrepl/building_servers.html
- https://nrepl.org/nrepl/ops.html
- https://docs.cider.mx/cider-nrepl/nrepl-api/ops.html (extended ops in emacs cider)
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

# TODO Editor connection
(setq nrepl-log-messages t)

## Connection fail
Need adjust decoder to be less strict?

### Cider
[2025-03-13T18:07:00.666929+00:00] server.debug: RECEIVED: d2:op5:clone2:id1:210:time-stamp29:2025-03-13 19:56:04.740577794e [] []
[2025-03-13T18:07:00.668829+00:00] server.error: UNKNOWN HANDLING ERROR: Arokettu\Bencode\Exceptions\ParseErrorException: Invalid order of dictionary keys: 'id' after 'op' in /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php:185 Stack trace: #0 [internal function]: Arokettu\Bencode\Engine\Reader->Arokettu\Bencode\Engine\{closure}() #1 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Bencode/Collection.php(18): iterator_to_array() #2 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(194): Arokettu\Bencode\Bencode\Collection->Arokettu\Bencode\Bencode\{closure}() #3 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(157): Arokettu\Bencode\Engine\Reader->finalizeDict() #4 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(83): Arokettu\Bencode\Engine\Reader->finalizeContainer() #5 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(55): Arokettu\Bencode\Engine\Reader->processChar() #6 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Decoder.php(62): Arokettu\Bencode\Engine\Reader->read()
### Calva
[2025-03-13T17:47:02.419766+00:00] server.debug: GOT: d2:op4:eval4:code4:*ns*2:id1:1e [] []
[2025-03-13T17:47:02.430963+00:00] server.error: loop error: Arokettu\Bencode\Exceptions\ParseErrorException: Invalid order of dictionary keys: 'code' after 'op' in /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php:185 Stack trace: #0 [internal function]: Arokettu\Bencode\Engine\Reader->Arokettu\Bencode\Engine\{closure}() #1 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Bencode/Collection.php(18): iterator_to_array() #2 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(194): Arokettu\Bencode\Bencode\Collection->Arokettu\Bencode\Bencode\{closure}() #3 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(157): Arokettu\Bencode\Engine\Reader->finalizeDict() #4 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(83): Arokettu\Bencode\Engine\Reader->finalizeContainer() #5 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(55): Arokettu\Bencode\Engine\Reader->processChar() #6 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Decoder.php(62): Arokettu\Bencode\Engine\Reader->read() #7 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Decoder.php(74): Arokettu\Bencode\Decoder->decodeStream() #8 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Bencode.php(27): Arokettu\Bencode\Decoder->decode() #9
