# Phel-nrepl (WIP)

`Status: initial wiring is mostly done, being used to write itself but not other programs yet`

Phel [Phel](https://phel-lang.org/) nREPL server implementation leveraging async [amphp/socket](https://amphp.org/socket) server (using PHP 8.1+ Fibers).

## TODO

- [ ] Fix requiring namespaces / files (Phel issue https://github.com/phel-lang/phel-lang/issues/766)
  - [x] `require` macro from repl-utils.phel works
  - [ ] Namespace switching with requires
- [ ] Investigate `defmacro` issue (`examples/250330-macro-issue.phel` / https://github.com/phel-lang/phel-lang/issues/784)

## Supported nREPL OPS
- [x] describe
- [x] clone
- [ ] eval
  - [x] single form
  - [ ] multiple forms
- Completions
  - Phel
    - [x] function names
	- [ ] namespaces (?)
  - PHP
    - [x] global function names
    - [x] class names
    - [ ] class methods
	- [ ] ...
- [ ] lookup
- [ ] info
- [ ] close

See ops map in `src/nrepl.phel` for more hints..

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

Cider does not recognize `\` as character to include in beginning of completion prefix due to `thingatpt.el` which needs custom setting such as:

```
(defun phel-bounds-of-symbol-at-point ()
  "Get bounds of symbol at point in Phel, including backslashes."
  (save-excursion
    (skip-syntax-backward "w_\\")
    (let ((start (point)))
      (skip-syntax-forward "w_\\")
      (when (> (point) start)
        (cons start (point))))))

(add-to-list 'bounds-of-thing-at-point-provider-alist
             (cons 'symbol (lambda ()
                            (when (eq major-mode 'phel-mode)
                              (phel-bounds-of-symbol-at-point)))))
```

### Calva
Logging: toggle nREPL logging enabled

- Startup command needs to be disabled/modified, that was solved with `(setq cider-repl-init-code "")` on Cider.
  - Answer probably in https://calva.io/customizing-jack-in-and-connect/
  - Pez: For the startup code, I think it is calva.autoEvaluateCode you need to update.
- Clojure standard lib is included in the completion quite aggressively which I didn't note with Cider.
  - Needs disabling somehow?
  - Pez: That’s probably clojure-lsp. You may need to disable some things in its config. But you could start with confirming by stopping clojure-lsp. (Can be done via the button in the status bar, and other ways.)

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
- https://github.com/squint-cljs/squint/blob/main/src/squint/repl/nrepl_server.cljs

Phel ILT project (used for some initial inspiration): https://codeberg.org/mmontone/interactive-lang-tools/src/branch/master/backends/phel/ilt.phel

## Later / Known issues (?)
  - Stream processing could be utilized if it brings benefit.
    - There's support in PHP bencode library that might work:
  https://sandfox.dev/php/bencode/decoding.html#working-with-streams
    - Amphp Byte Stream Json decoder example:
  https://github.com/amphp/byte-stream/blob/daa00f2efdbd71565bf64ffefa89e37542addf93/src/functions.php#L168
  - The bencode library originally used (in composer.json) is not used for now because of Cider issue (https://github.com/clojure-emacs/cider/issues/3786)

## License

Licensed under the [MIT license](https://opensource.org/licenses/MIT).

### Debugging
#### nREPL connection
Using https://github.com/lambdaisland/nrepl-proxy
```
vendor/bin/phel run src/nrepl.phel
clojure -Sdeps '{:deps {com.lambdaisland/nrepl-proxy {:mvn/version "0.2.8-alpha"}}}' -X lambdaisland.nrepl-proxy/start :port 9999 :attach 8888
```

Connect to proxy listening to port 9999.

#### Phel internals
Logging from Phel internals is available via [Patchwork](https://github.com/phel-lang/phel-lang/discussions/796).
Modify `tracer.php` with classes/modules to trace and start script with `./pphel run src/nrepl.phel`, tail log files created in the folder during execution.


# TODO  (after this section is just some mess)

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
