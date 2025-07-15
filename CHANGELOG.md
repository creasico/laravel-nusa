# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

### [0.1.15](https://github.com/creasico/laravel-nusa/compare/v0.1.14...v0.1.15) (2025-07-15)


### Features

* **ci:** remove laravel `10.x` from ci ([97309ce](https://github.com/creasico/laravel-nusa/commit/97309ce72f888c148b9a993fecf1c3e5fd12b8be))
* **dev:** enhance `nusa:generate-static` command ([#165](https://github.com/creasico/laravel-nusa/issues/165)) ([de74080](https://github.com/creasico/laravel-nusa/commit/de74080af1fcfdb6e68bab0affad796f206cf1cb))
* **docs:** add `.agent.md` as context file or rule for ai agent ([f776ac3](https://github.com/creasico/laravel-nusa/commit/f776ac3319e7c790e232b08cb44abc4d5d39eb57))
* **docs:** init documentation website using vitepress ([47d885f](https://github.com/creasico/laravel-nusa/commit/47d885f0ae6004e229dfcaa540a7b3a7b5283012))

### [0.1.14](https://github.com/creasico/laravel-nusa/compare/v0.1.13...v0.1.14) (2025-07-09)


### Features

* **deps:** bump workbench/submodules/cahyadsn-wilayah_boundaries from `a8f919e` to `be7b869` ([#158](https://github.com/creasico/laravel-nusa/issues/158)) ([34fd04f](https://github.com/creasico/laravel-nusa/commit/34fd04fe7f39076c8f518496513bc52afe940390))
* **dev:** enhance changes stat generation ([e651a4f](https://github.com/creasico/laravel-nusa/commit/e651a4f28069bce71fabba2baddb9b378bcac389))
* enhance import process and database diff reporting ([#160](https://github.com/creasico/laravel-nusa/issues/160)) ([eebcfb6](https://github.com/creasico/laravel-nusa/commit/eebcfb6b9e87db5c9ca69d2b651c56d339d54934))
* show detailed changes from upstream ([#159](https://github.com/creasico/laravel-nusa/issues/159)) ([aa2a7f6](https://github.com/creasico/laravel-nusa/commit/aa2a7f68b1c2250c61f9b5359289678f2e706d1d))


### Bug Fixes

* **ci:** ensure `dist` workflow got the latest data from upstream ([4aed5e9](https://github.com/creasico/laravel-nusa/commit/4aed5e9071e9299aa8c02ae9b9f08b38918f2946))

### [0.1.13](https://github.com/creasico/laravel-nusa/compare/v0.1.12...v0.1.13) (2025-07-02)


### ⚠ BREAKING CHANGES

* The area codes are no longer stored as numeric value.
* refactor the way importing data from `upstream` (#120)

### Features

* add `coordinates` field ([#143](https://github.com/creasico/laravel-nusa/issues/143)) ([cef1b71](https://github.com/creasico/laravel-nusa/commit/cef1b717062fdeabf78f94b9bd5b8d819395c16d))
* add `nusa:generate-static` command ([62ff7f1](https://github.com/creasico/laravel-nusa/commit/62ff7f170c13a402f33d3571218c05a795ca2c6f))
* add ability to create sqlite file on branch other than `main` ([1fa131e](https://github.com/creasico/laravel-nusa/commit/1fa131e4e66263acdd5010fa122e3f23b395a300))
* **ci:** rearrange actions workflows ([#144](https://github.com/creasico/laravel-nusa/issues/144)) ([d62e3d8](https://github.com/creasico/laravel-nusa/commit/d62e3d8ec094066820f0fd7171363306b01008cb))
* **dev:** init `nusa:dist` command ([#147](https://github.com/creasico/laravel-nusa/issues/147)) ([ae00383](https://github.com/creasico/laravel-nusa/commit/ae003837c98a742ce5b106ad8855549d41859dfd))
* refactor the way importing data from `upstream` ([#120](https://github.com/creasico/laravel-nusa/issues/120)) ([443e4e9](https://github.com/creasico/laravel-nusa/commit/443e4e95e4ebc66775f57c032f63c55cf9e46f3f))
* update composer scripts to accommodate dev ([8efb4e7](https://github.com/creasico/laravel-nusa/commit/8efb4e7bd839cfd52db678127ef70d24b8cfd987))
* use legal area code format ([#142](https://github.com/creasico/laravel-nusa/issues/142)) ([31915e5](https://github.com/creasico/laravel-nusa/commit/31915e518eb7bae5c60336270599088e13768cfe))

### [0.1.12](https://github.com/creasico/laravel-nusa/compare/v0.1.11...v0.1.12) (2025-03-13)


### Features

* **db:** update embeded db due to upstream changes, see [#114](https://github.com/creasico/laravel-nusa/issues/114) [#118](https://github.com/creasico/laravel-nusa/issues/118) & [#119](https://github.com/creasico/laravel-nusa/issues/119) ([ac1b312](https://github.com/creasico/laravel-nusa/commit/ac1b312e519d2405c9b8e9fb70a2527a7bfadb91))
* provide `.geojson` data for provinces ([#110](https://github.com/creasico/laravel-nusa/issues/110)) ([0b8731e](https://github.com/creasico/laravel-nusa/commit/0b8731e08b0ea862fef81a1c116af99a09050644))


### Bug Fixes

* **ci:** unable to regenerate stat file even `-w` option is passed to the `stat` command ([fddc68f](https://github.com/creasico/laravel-nusa/commit/fddc68f0212d3267da522f7ea4afe25e08dd9d0d))

### [0.1.11](https://github.com/creasico/laravel-nusa/compare/v0.1.10...v0.1.11) (2025-01-12)


### Features

* **ci:** add `codeclimate` config ([3cbff63](https://github.com/creasico/laravel-nusa/commit/3cbff635b5810f83d392aa5942f72ac5a985aeeb))
* **db:** init boundaries data using `cahyadsn/wilayah_boundaries` ([#104](https://github.com/creasico/laravel-nusa/issues/104)) ([ac10c2f](https://github.com/creasico/laravel-nusa/commit/ac10c2fa9421dde0f441906086c1691096ca17a1))
* **dev:** call `vendor:publish` and `migrate:fresh` from `nusa:import` ([3bc71c6](https://github.com/creasico/laravel-nusa/commit/3bc71c621904ed68c9ee24ff34d3d184c3a5496d))


### Bug Fixes

* fix known issue on `cahyadsn/wilayah_boundaries` ([#105](https://github.com/creasico/laravel-nusa/issues/105)) ([b10e096](https://github.com/creasico/laravel-nusa/commit/b10e09613765d266572d80aa3dc9f8fbe6e63b8d))

### [0.1.10](https://github.com/creasico/laravel-nusa/compare/v0.1.9...v0.1.10) (2025-01-11)


### Features

* **db:** swap `w3appdev/kodepos` to `cahyadsn/wilayah_kodepos` ([#103](https://github.com/creasico/laravel-nusa/issues/103)) ([58b5d43](https://github.com/creasico/laravel-nusa/commit/58b5d43d4c80a38273f108d4c8f1bf1db4f8f863))
* **db:** update embeded db due to upstream changes, see e2ffdf9 ([3e5a96c](https://github.com/creasico/laravel-nusa/commit/3e5a96ca648dcb66c901285bda3713f1b6ae155a))
* **dev:** update `nusa:stat` ([5fe7b00](https://github.com/creasico/laravel-nusa/commit/5fe7b009473cc4e6ed015239f8818840f39a8297))
* switch back to `projek-xyz/actions` workflows ([#92](https://github.com/creasico/laravel-nusa/issues/92)) ([f325387](https://github.com/creasico/laravel-nusa/commit/f325387c43de3c941ed0f9a045f7e4e5e0540891)), closes [projek-xyz/actions#1](https://github.com/projek-xyz/actions/issues/1)
* **test:** disable process timeout while running tests ([8755fca](https://github.com/creasico/laravel-nusa/commit/8755fca6057782907fc5f91ead6f2eae327a5435))


### Bug Fixes

* **dev:** fix issue regarding upstream artifacts ([#101](https://github.com/creasico/laravel-nusa/issues/101)) ([466afb3](https://github.com/creasico/laravel-nusa/commit/466afb381297c34989c5b79e0ae75a96a5014b68))
* fix `csv` file generation issue ([4760249](https://github.com/creasico/laravel-nusa/commit/4760249ac4fffde63cc55b06d1e893740bb39701))
* upstream field changed from `paths` to `path` ([667a860](https://github.com/creasico/laravel-nusa/commit/667a860dd1dc0b530510e777b898ac5c8fc66043))

### [0.1.9](https://github.com/creasico/laravel-nusa/compare/v0.1.8...v0.1.9) (2024-10-06)


### Features

* **ci:** update ci config to use workflow from `feryardiant/actions` ([#90](https://github.com/creasico/laravel-nusa/issues/90)) ([612ce59](https://github.com/creasico/laravel-nusa/commit/612ce59246db9025b8653dfb3b6c803523bfdfae))

### [0.1.8](https://github.com/creasico/laravel-nusa/compare/v0.1.7...v0.1.8) (2024-08-06)


### Features

* add `pint` config to exclude `submodules` dir ([404032d](https://github.com/creasico/laravel-nusa/commit/404032d48d02dea2744d5e679a7eb4b315cb5d18))
* **db:** create basic migration for `addresses` tabel ([62ee6d3](https://github.com/creasico/laravel-nusa/commit/62ee6d3239f994698bf9208c0657b71615e39e6f)), closes [#85](https://github.com/creasico/laravel-nusa/issues/85)
* init stat command ([ba48cdb](https://github.com/creasico/laravel-nusa/commit/ba48cdbf3f8b2297f76f1958e0d4c79ad82db017))


### Bug Fixes

* **test:** fix test issue with address model relations ([a95b38a](https://github.com/creasico/laravel-nusa/commit/a95b38ad76592f16389bee3696f9f85e111bf564))

### [0.1.7](https://github.com/creasico/laravel-nusa/compare/v0.1.6...v0.1.7) (2024-03-13)


### Features

* **ci:** init tests on laravel 11 ([975fe02](https://github.com/creasico/laravel-nusa/commit/975fe021773137662b77bc2974344c0469e534e3))

### [0.1.6](https://github.com/creasico/laravel-nusa/compare/v0.1.5...v0.1.6) (2024-02-06)

### [0.1.5](https://github.com/creasico/laravel-nusa/compare/v0.1.4...v0.1.5) (2023-11-08)


### Features

* **deps:** let dependabot also manage submodules ([09821d4](https://github.com/creasico/laravel-nusa/commit/09821d49b35d563a61224d8ca9e929bab797870b))
* **dev:** add thunder-client collection ([0efe822](https://github.com/creasico/laravel-nusa/commit/0efe82262fa46995433ae2aa00f1009c18bd4671))
* exclude `JSON` and `CSV` resources ([2932794](https://github.com/creasico/laravel-nusa/commit/2932794de6789ae6380c00411d3f9b1b4e7bc60e)), closes [#48](https://github.com/creasico/laravel-nusa/issues/48)


### Bug Fixes

* **api:** don't execute query on empty params ([08e902f](https://github.com/creasico/laravel-nusa/commit/08e902f0482c918c3cea44b366d3e2caa6b0c22b))

### [0.1.4](https://github.com/creasico/laravel-nusa/compare/v0.1.3...v0.1.4) (2023-11-02)


### Features

* **api:** add `with` query string to include certain fields or relations ([94580b2](https://github.com/creasico/laravel-nusa/commit/94580b229ca8373ec908749beeb865dcda25f902))
* **api:** add ability to filter villages by `postal_code` ([ffde718](https://github.com/creasico/laravel-nusa/commit/ffde718e32dd1c46cb55eb3d59fc56f0c3f50aeb))
* **api:** add ability to use `search` query on subroutes ([a0949da](https://github.com/creasico/laravel-nusa/commit/a0949daa9d56eb94a89f6076e4e4dae76d2a64f9))
* **dev:** enable vscode debuging config ([70c74f3](https://github.com/creasico/laravel-nusa/commit/70c74f3d5a4a4aa294ba34065afbd4c65324d7f7))


### Bug Fixes

* **api:** fix `with` query string does not return expected output ([62bc1a6](https://github.com/creasico/laravel-nusa/commit/62bc1a675539cdcafd4b1f7686c083cf0abd0edc))

### [0.1.3](https://github.com/creasico/laravel-nusa/compare/v0.1.2...v0.1.3) (2023-09-24)


### Bug Fixes

* fix code styles ([03e157d](https://github.com/creasico/laravel-nusa/commit/03e157d1361c291ea6b885e7349d069d8f29e062))
* fix some false positive test on province resources ([a20ca86](https://github.com/creasico/laravel-nusa/commit/a20ca86cab732cb97fe2c7d0f8939a2c4ebf13fa))

### [0.1.2](https://github.com/creasico/laravel-nusa/compare/v0.1.1...v0.1.2) (2023-08-30)


### Features

* **model:** adds ability to retrieve list of `postal_codes` ([0e15f6d](https://github.com/creasico/laravel-nusa/commit/0e15f6d99fca687b501d81e7449ff2bbd72be724))

### [0.1.1](https://github.com/creasico/laravel-nusa/compare/v0.1.0...v0.1.1) (2023-07-20)


### Features

* add new interface and trait to helps models with coordinate info ([be46731](https://github.com/creasico/laravel-nusa/commit/be4673139b214198241efc4baf80144cdea9b8ae))
* **deps:** utilize `package:test` command from `orchestra/testbench` to run the tests ([1da613f](https://github.com/creasico/laravel-nusa/commit/1da613f3b0421f438c2dea77884ee8015c0e8587))
* register `Province`, `Regency`, `District` and `Village` interface as container ([ba01162](https://github.com/creasico/laravel-nusa/commit/ba011628d90e51284ddc65458678eef0706349cd))

## [0.1.0](https://github.com/creasico/laravel-nusa/compare/v0.0.6...v0.1.0) (2023-07-16)


### ⚠ BREAKING CHANGES

* get rid of unnecessary interface
* rename address classnames

### Features

* add more model traits to helps extendability of address ([6a118c2](https://github.com/creasico/laravel-nusa/commit/6a118c20f6b322b9444e8da8b215fb601bcbe6b8))


### Bug Fixes

* **tests:** fix creasico/laravel-package[#47](https://github.com/creasico/laravel-nusa/issues/47) ([534b903](https://github.com/creasico/laravel-nusa/commit/534b903cb325abbabf508ad6f79ba17e5ac67626)), closes [creasico/laravel-package#48](https://github.com/creasico/laravel-package/issues/48)


* get rid of unnecessary interface ([323a7bc](https://github.com/creasico/laravel-nusa/commit/323a7bc1648b9323333dfcced15e124e3705c1f6))
* rename address classnames ([c549f97](https://github.com/creasico/laravel-nusa/commit/c549f971e67acbbfb7f0c49675f27bcea501695f))

### [0.0.6](https://github.com/creasico/laravel-nusa/compare/v0.0.5...v0.0.6) (2023-07-14)


### Features

* **db:** indexing every `name` fields on every tables ([40270fe](https://github.com/creasico/laravel-nusa/commit/40270fed9a2b8ed28d17b9f5aadcc8578fc88c1c))
* initialize codeclimate integrations ([9d4070b](https://github.com/creasico/laravel-nusa/commit/9d4070b6ce8437fb920248cf83bb2508b70a4ee9))
* **model:** add ability to `search` by `code` or `name` on each models ([ad8656d](https://github.com/creasico/laravel-nusa/commit/ad8656d643f0130d3f0804d930d299da5f36a33b))

### [0.0.5](https://github.com/creasico/laravel-nusa/compare/v0.0.4...v0.0.5) (2023-07-07)


### Features

* add support to morph one or many addresses ([b75e394](https://github.com/creasico/laravel-nusa/commit/b75e394969a28d2f051024cf88f8f86f81c5ce4a))
* split distributed and testing databases ([7962e35](https://github.com/creasico/laravel-nusa/commit/7962e35ca09a8298cd1dc9431d50110d2a30afe5))
* use sub-directory for `config` file ([16a4110](https://github.com/creasico/laravel-nusa/commit/16a4110b4637be5e903faca2df6e38ecb36e4d14))

### [0.0.4](https://github.com/creasico/laravel-nusa/compare/v0.0.3...v0.0.4) (2023-07-03)


### Features

* add `addressable` config for the sake of customizable ([7951bba](https://github.com/creasico/laravel-nusa/commit/7951bbaddd1e97c90fdd5ba01e7fa649d5209b0f))

### [0.0.3](https://github.com/creasico/laravel-nusa/compare/v0.0.2...v0.0.3) (2023-07-03)


### Features

* add trait to help associate with addresses ([20e4b62](https://github.com/creasico/laravel-nusa/commit/20e4b62ddd77ba8d24ed39701a51edcbe57ad720))

### [0.0.2](https://github.com/creasico/laravel-nusa/compare/v0.0.1...v0.0.2) (2023-06-26)


### Features

* add basic model for address ([642d88e](https://github.com/creasico/laravel-nusa/commit/642d88e2fc8507daf11c92d7993a6d18e0786fbf))
* define model contract for easy extendability ([edbfd06](https://github.com/creasico/laravel-nusa/commit/edbfd065de64bf849120e7c493340ab3eef65cf1))

### 0.0.1 (2023-06-23)
