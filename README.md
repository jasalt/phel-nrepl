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

# Editor connection workarounds
For client debugging use:
- Cider: (setq nrepl-log-messages t)
- Calva: toggle nREPL logging enabled

## TODO cider-info-form eval fails
Workaround: (setq cider-info-form "")
https://github.com/clojure-emacs/cider/blob/master/cider-client.el#L529

[2025-03-14T11:10:23.370182+00:00] server.debug: RECEIVED: d2:op4:eval4:code591: (do   (require 'clojure.java.io)   (require 'clojure.walk)    (if-let [var (resolve 'php/dl)]     (let [info (meta var)]       (-> info           (update :ns str)           (update :name str)           (update :file (comp str clojure.java.io/resource))           (cond-> (:macro info) (update :macro str))           (cond-> (:special-form info) (update :special-form str))           (cond-> (:protocol info) (update :protocol str))           (cond-> (:arglists info) (update :arglists str))           (assoc :arglists-str (str (:arglists info)))           (clojure.walk/stringify-keys))))) 7:sessioni99e2:id2:22e [] []
[2025-03-14T11:10:23.370663+00:00] server.debug:  (do   (require 'clojure.java.io)   (require 'clojure.walk)    (if-let [var (resolve 'php/dl)]     (let [info (meta var)]       (-> info           (update :ns str)           (update :name str)           (update :file (comp str clojure.java.io/resource))           (cond-> (:macro info) (update :macro str))           (cond-> (:special-form info) (update :special-form str))           (cond-> (:protocol info) (update :protocol str))           (cond-> (:arglists info) (update :arglists str))           (assoc :arglists-str (str (:arglists info)))           (clojure.walk/stringify-keys)))))  [] []
[2025-03-14T11:10:23.371125+00:00] server.error: UNKNOWN HANDLING ERROR: Phel\Compiler\Domain\Reader\Exceptions\ReaderException: Cannot read from whitespace or comments in /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Domain/Reader/Exceptions/ReaderException.php:27 Stack trace: #0 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Application/Reader.php(49): Phel\Compiler\Domain\Reader\Exceptions\ReaderException::forNode() #1 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/CompilerFacade.php(157): Phel\Compiler\Application\Reader->read() #2 /tmp/__phel8kZaZM(24): Phel\Compiler\CompilerFacade->read() #3 /tmp/__phel8kZaZM(25): Phel\Lang\AbstractFn@anonymous->{closure}() #4 /tmp/__phel8kZaZM(27): Phel\Lang\AbstractFn@anonymous->{closure}()...

## TODO clojure repl require on cider connect fails
There's a failure on Cider startup when it attempts to resolve and load `clojure.main/repl-requires`.

```
(when-let [requires (resolve 'clojure.main/repl-requires)]
  (clojure.core/apply clojure.core/require @requires))
```

No such namespace exists, neither `when-let` or `resolve`.


Can be overriden with `(setq cider-repl-init-code "")`, could be require https://github.com/phel-lang/phel-lang/blob/main/src/php/Run/Domain/Repl/startup.phel:
```
(:require phel\repl :refer [doc require use print-colorful println-colorful])
```
-- https://docs.cider.mx/cider/repl/basic_usage.html#loading-repl-utility-functions


### exception:

crash:
[2025-03-14T07:06:27.234180+00:00] server.debug: RECEIVED: d2:op4:eval4:code113:(when-let [requires (resolve 'clojure.main/repl-requires)]   (clojure.core/apply clojure.core/require @requires))4:file54:*cider-repl phel-snake-online/src:localhost:8888(clj)*4:linei9e6:columni1e30:nrepl.middleware.print/stream?1:128:nrepl.middleware.print/print25:cider.nrepl.pprint/pprint28:nrepl.middleware.print/quotai1048576e34:nrepl.middleware.print/buffer-sizei4096e30:nrepl.middleware.print/optionsd12:right-margini70ee24:inhibit-cider-middleware4:true7:sessioni99e2:id1:7e [] []
[2025-03-14T07:06:27.235188+00:00] server.debug: (when-let [requires (resolve 'clojure.main/repl-requires)]   (clojure.core/apply clojure.core/require @requires)) [] []
[2025-03-14T07:06:27.235620+00:00] server.error: UNKNOWN HANDLING ERROR: Phel\Compiler\Domain\Lexer\Exceptions\LexerValueException: Cannot lex string after at column 43 in string:2 in /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Domain/Lexer/Exceptions/LexerValueException.php:15 Stack trace: #0 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Application/Lexer.php(92): Phel\Compiler\Domain\Lexer\Exceptions\LexerValueException::unexpectedLexerState() #1 [internal function]: Phel\Compiler\Application\Lexer->lexStringGenerator() #2 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Domain/Lexer/TokenStream.php(33): Generator->next() #3 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Application/Parser.php(91): Phel\Compiler\Domain\Lexer\TokenStream->next() #4 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Domain/Parser/ExpressionParser/ListParser.php(37): Phel\Compiler\Application\Parser->readExpression() #5 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Application/Parser.php(180): Phel\Compiler\Domain\Parser\ExpressionParser\ListParser->parse() #6 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Application/Parser.php(101): Phel\Compiler\Application\Parser->parseFnListNode() #7 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Domain/Parser/ExpressionParser/ListParser.php(37): Phel\Compiler\Application\Parser->readExpression() #8 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Application/Parser.php(180): Phel\Compiler\Domain\Parser\ExpressionParser\ListParser->parse() #9 /home/user/dev/phel-nrepl/vendor/phel-lang/phel-lang/src/php/Compiler/Application/Parser.php(101): Phel\Compiler\Application\Parser->parseFnListNode()...
## DONE Bencode library is too strict
Workaround was to use less strict decoder from another library for now.

https://github.com/clojure-emacs/cider/issues/3786

### Cider
[2025-03-13T18:07:00.666929+00:00] server.debug: RECEIVED: d2:op5:clone2:id1:210:time-stamp29:2025-03-13 19:56:04.740577794e [] []
[2025-03-13T18:07:00.668829+00:00] server.error: UNKNOWN HANDLING ERROR: Arokettu\Bencode\Exceptions\ParseErrorException: Invalid order of dictionary keys: 'id' after 'op' in /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php:185 Stack trace: #0 [internal function]: Arokettu\Bencode\Engine\Reader->Arokettu\Bencode\Engine\{closure}() #1 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Bencode/Collection.php(18): iterator_to_array() #2 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(194): Arokettu\Bencode\Bencode\Collection->Arokettu\Bencode\Bencode\{closure}() #3 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(157): Arokettu\Bencode\Engine\Reader->finalizeDict() #4 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(83): Arokettu\Bencode\Engine\Reader->finalizeContainer() #5 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(55): Arokettu\Bencode\Engine\Reader->processChar() #6 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Decoder.php(62): Arokettu\Bencode\Engine\Reader->read()...
### Calva
[2025-03-13T17:47:02.419766+00:00] server.debug: GOT: d2:op4:eval4:code4:*ns*2:id1:1e [] []
[2025-03-13T17:47:02.430963+00:00] server.error: loop error: Arokettu\Bencode\Exceptions\ParseErrorException: Invalid order of dictionary keys: 'code' after 'op' in /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php:185 Stack trace: #0 [internal function]: Arokettu\Bencode\Engine\Reader->Arokettu\Bencode\Engine\{closure}() #1 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Bencode/Collection.php(18): iterator_to_array() #2 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(194): Arokettu\Bencode\Bencode\Collection->Arokettu\Bencode\Bencode\{closure}() #3 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(157): Arokettu\Bencode\Engine\Reader->finalizeDict() #4 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(83): Arokettu\Bencode\Engine\Reader->finalizeContainer() #5 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Engine/Reader.php(55): Arokettu\Bencode\Engine\Reader->processChar() #6 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Decoder.php(62): Arokettu\Bencode\Engine\Reader->read() #7 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Decoder.php(74): Arokettu\Bencode\Decoder->decodeStream() #8 /home/user/dev/phel-nrepl/vendor/arokettu/bencode/src/Bencode.php(27): Arokettu\Bencode\Decoder->decode() #9...
