# ATRIUM / Bahnhof Grenzau: production domains and retired previews

Authorized by Oliver on 28 October 2026. Production lives at `atrium191.de` and
`bahnhof-grenzau.de`. The old `atrium191-rb.odpc.de` and
`bahnhof-grenzau.odpc.de` must stop serving duplicate public HTML without
breaking production resources.

The consolidation script checks all production sitemap pages, the two Bahnhof
legal pages, every old ATRIUM page destination, first-party scripts, styles,
images, fonts and linked downloads. Actual resource dependencies on a retired
host stop the release. Old domain names occurring as text are reported
separately; three legal pages still describe the earlier preview domain and
need a later editorial review.

Only the two retired document roots' `.htaccess` files are modified. Every
redirect is restricted to its exact old hostname. Prior rules remain intact;
administrative paths and static media continue through those rules as a
fallback. No WordPress installation, database, image or download is deleted.

Known legacy paths are mapped to equivalent production content: ATRIUM strips
the old `index.php/` prefix; Bahnhof's `impressum.html` and `datenschutz.html`
lead to the actual WordPress pages `?page_id=7` and `?page_id=8`. Production
sitemaps receive old sitemap requests.

The runner verifies production pages and assets both before and after the
change and compares production HTML fingerprints. Redirect status codes and
destinations are tested. Failure restores both prior configurations. The
prior configurations are also retained in an encrypted Actions artifact;
decryption uses the existing protected SFTP secret, not a key placed in the
repository or logs.

Future releases of the old preview/bootstrap scripts must preserve this
retirement block. Recreating a prior `.htaccess` wholesale can reactivate
duplicate HTML. The consolidation workflow can be rerun idempotently after an
intentional old-root maintenance release.

This is public retirement through redirects and retained fallback assets, not
physical deletion. Final filesystem removal is a separate operation requiring
a longer observation period and another dependency check.
