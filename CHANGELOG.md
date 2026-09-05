# Changelog

## [0.1.1](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/compare/v0.1.0...v0.1.1) (2026-09-05)


### Bug Fixes

* register the migrations under the plugin namespace ([1834c80](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/1834c80ed9f051389d4987b27a9a8be5b305b300))

## 0.1.0 (2026-09-05)


### Features

* adjust a wallet balance from the admin ([e9f802f](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/e9f802ff1d13b01f96c3d714edb0c2e0481405b1))
* admin wallet management with manual adjustments ([1082afa](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/1082afa4faa98728f52de1f38505265f8b2661b7))
* create token packs from a dedicated admin form ([a788ebb](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/a788ebb45e8dbed3ceae84e17294216e1f116a80))
* prepaid token wallet with packs, ledger and consumption ([3d0e7e9](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/3d0e7e93083fd5d6bebfe8c91ea4fcf5a62bf70a))
* show the balance and the ledger in the customer account ([c5f87e9](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/c5f87e9aee3a4a3ef409f9f4d196bb15240fa43c))
* token overview on the customer page, full ledger history in admin ([2916def](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/2916def7f96b61c4227850820e5f356b89087bcc))
* translate the plugin in english and french ([dad5a92](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/dad5a92fb18ac4a7467b5940df23e2f31409e567))


### Bug Fixes

* build the operation through a factory like every other entity ([334193b](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/334193bb017aba01a4425f360f3364e66385d7a7))
* drop the dead permission directive on the wallet adjust route ([acd7205](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/acd72050e7f4f9b18897f7f3a998599183229cd5))
* issues found while reading the plugin file by file ([3f0006b](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/3f0006bfeafaec877d1083254277852cc82ba72a))
* label consumption entries with the price they paid for ([a53b32d](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/a53b32d5b28c4328cb91ec89ed6d6de3b690b683))
* make the balance queries work on postgresql ([07673aa](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/07673aa445644af1081815bffbd619fdc800669c))
* refuse token amounts the integer column cannot store ([acee251](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/acee251e4bd076dfaa3290bec37dadc54239ac32))
* reset the memoised balance between requests ([b18fb6a](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/b18fb6a1e8c8615c451ff5fa31886f4fc12f4a6a))
* restore the resource labels Sylius resolves by convention ([ce811c7](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/ce811c7619862dcc4c62426d9c5d6d57344bca8d))
* show the customer what each movement was for ([e0ff79e](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/e0ff79e47e1a7d209ce70e4495da8c4318873f22))
* stop a replayed credit with an operation registry ([f6d4492](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/f6d44929e5d03039dde161c37820854729d354d3))


### Performance Improvements

* fetch the customer alongside the wallet in the admin grid ([d9e005a](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/d9e005a30619ae89afc2e4c37476f2b3b6d5a86f))
* materialise the wallet balance and make expiration an event ([97b5a68](https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/commit/97b5a6882e16587d86e4ae3263c1f49f1bc0023f))
