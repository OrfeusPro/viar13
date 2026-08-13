# Services Catalog Matrix Check (2026-03-02)

- Endpoint: `/api/sa/services-catalog`
- Params: `lang in {ru,uk,en}`, `country_code in {LV,FI,DE,ZZZ}`, `include_inactive=false`
- Auth: `X-Api-Key`
- Runtime: local API (`https://viarcanvas.loc`)

| lang | country req | http | status | meta.country_code | multiplier | services | first service | first price |
|---|---|---:|---|---|---:|---:|---|---:|
| ru | LV | 200 | ok | LV | 1 | 12 | HM-27 (CUSTOM CARICATURE) | 60 |
| ru | FI | 200 | ok | FI | 1.3 | 12 | HM-27 (CUSTOM CARICATURE) | 78 |
| ru | DE | 200 | ok | DE | 1.2 | 12 | HM-27 (CUSTOM CARICATURE) | 72 |
| ru | ZZZ | 200 | ok | LV | 1 | 12 | HM-27 (CUSTOM CARICATURE) | 60 |
| uk | LV | 200 | ok | LV | 1 | 12 | HM-27 (CUSTOM CARICATURE) | 60 |
| uk | FI | 200 | ok | FI | 1.3 | 12 | HM-27 (CUSTOM CARICATURE) | 78 |
| uk | DE | 200 | ok | DE | 1.2 | 12 | HM-27 (CUSTOM CARICATURE) | 72 |
| uk | ZZZ | 200 | ok | LV | 1 | 12 | HM-27 (CUSTOM CARICATURE) | 60 |
| en | LV | 200 | ok | LV | 1 | 12 | HM-27 (CUSTOM CARICATURE) | 60 |
| en | FI | 200 | ok | FI | 1.3 | 12 | HM-27 (CUSTOM CARICATURE) | 78 |
| en | DE | 200 | ok | DE | 1.2 | 12 | HM-27 (CUSTOM CARICATURE) | 72 |
| en | ZZZ | 200 | ok | LV | 1 | 12 | HM-27 (CUSTOM CARICATURE) | 60 |

## Findings
- All requests returned `HTTP 200` and `status=ok`.
- Country handling works: `FI` and `DE` apply higher multipliers; unknown `ZZZ` falls back to `LV`.
- Service count is stable (`12`) across matrix.
- First service name is currently English for all `lang` values; menu translations are not being resolved in this runtime dataset.
