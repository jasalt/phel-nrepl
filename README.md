# Phel-nrepl (WIP)

`Status: work in progress, some initial wiring done`

Phel [Phel](https://phel-lang.org/) nREPL server implementation leveraging [amphp/socket](https://amphp.org/socket) for async processing.

## TODO

- [ ] Fix requiring namespaces / files (Phel issue https://github.com/phel-lang/phel-lang/issues/766)
- [ ] Investigate `defmacro` issue (`examples/250330-macro-issue.phel` / https://github.com/phel-lang/phel-lang/issues/784)

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

Connection with Emacs Cider and Calva work mostly.

### Cider
Logging: (setq nrepl-log-messages t)

Developed using clojure-mode with small tweaks https://codeberg.org/jasalt/.emacs.d/src/branch/main/personal/phel.el.

### Calva
Logging: toggle nREPL logging enabled

- Startup command needs to be disabled/modified, that was solved with `(setq cider-repl-init-code "")` on Cider.
  - Answer probably in https://calva.io/customizing-jack-in-and-connect/
- Clojure standard lib is included in the completion quite aggressively which I didn't note with Cider.
  - Needs disabling somehow?


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


### Debugging

Using https://github.com/lambdaisland/nrepl-proxy
```
vendor/bin/phel run src/nrepl.phel
clojure -Sdeps '{:deps {com.lambdaisland/nrepl-proxy {:mvn/version "0.2.8-alpha"}}}' -X lambdaisland.nrepl-proxy/start :port 9999 :attach 8888
```

Connect to proxy listening to port 9999.

## Supported nREPL OPS
- describe
- clone (does not give unique id's)
- eval (simplistic)
- completions (completion info wip)

Read sources for more hints, docs are not up to date.

## Misc

- The bencode library originally used (in composer.json) is not used for now because of Cider issue (https://github.com/clojure-emacs/cider/issues/3786)


# TODO
## function info
(defconst cider-info-form "
(do
  (require 'clojure.java.io)
  (require 'clojure.walk)

  (if-let [var (resolve '%s)]
    (let [info (meta var)]
      (-> info
          (update :ns str)
          (update :name str)
          (update :file (comp str clojure.java.io/resource))
          (cond-> (:macro info) (update :macro str))
          (cond-> (:special-form info) (update :special-form str))
          (cond-> (:protocol info) (update :protocol str))
          (cond-> (:arglists info) (update :arglists str))
          (assoc :arglists-str (str (:arglists info)))
          (clojure.walk/stringify-keys)))))
")

## cider-enlighten-mode eval not working
[2025-03-16T17:27:16.054509+00:00] server.debug: RECEIVED: d2:ns4:user2:op4:eval4:code7:(+ 1 1)9:enlighten4:true4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei7e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei7e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei7e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei7e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei7e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei1e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei3e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei3e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei7e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei5e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei5e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei5e6:columni1e4:file46:/home/user/dev/phel-snake-online/src/demo.phel4:linei5e6:columni1e28:nrepl.middleware.print/print21:cider.nrepl.pprint/pr30:nrepl.middleware.print/stream?le28:nrepl.middleware.print/quotai1048576e7:sessioni99e2:id2:79e [] []
[2025-03-16T17:27:16.054731+00:00] server.error: UNKNOWN HANDLING ERROR: Rhilip\Bencode\ParseErrorException: Duplicate Dictionary key exist before: file in /home/user/dev/phel-nrepl/vendor/rhilip/bencode/src/Bencode.php:75 Stack trace: #0 /tmp/__phelb54hJY(17): Rhilip\Bencode\Bencode::decode() #1 /tmp/__pheli7XaNg(53): Phel\Lang\AbstractFn@anonymous->__invoke() #2 /home/user/dev/phel-nrepl/vendor/amphp/amp/src/functions.php(33): Phel\Lang\AbstractFn@anonymous->__invoke() #3 /home/user/dev/phel-nrepl/vendor/revolt/event-loop/src/EventLoop/Internal/AbstractDriver.php(430): Amp\{closure}()

# Misc notes
## `->getHost()` method missing found, amphp docs have issue? https://amphp.org/socket#handling-connections
Working example:
```
(def client-address (php/-> socket (getRemoteAddress)))
(php/-> client-address (getAddress))
```
