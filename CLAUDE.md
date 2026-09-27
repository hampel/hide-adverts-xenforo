# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Hide Adverts is a XenForo 2.x add-on (`Hampel/HideAdverts`, requires XF 2.1.0+) that
lets permitted members opt out of adverts, and lets admins suppress adverts per node and per user
group. The repository root is the add-on directory, `src/addons/Hampel/HideAdverts/` inside a
XenForo install; there is no `src/` of its own and no Composer dependencies.

## Two suppression paths, and only one is enforced in code

**The node and user-group options are enforced by the add-on; the permission is not.** Reading the
code with that in mind saves chasing a check that does not exist.

- **Nodes and user groups** — `XF/Template/Templater.php` extends `callAdsMacro()` and returns an
  empty string before core renders the `_ads` macro for a position. A visitor in any group listed
  in `hampelHideAdvertsDisabledUsergroups` sees no adverts regardless of permission or preference.
  The node check parses the reply's `containerKey` (`node-<id>`), so it only fires on pages whose
  controller sets one.
- **Permission plus preference** — the `general.hampelHideAdverts` permission and the
  `xf_user_option.hide_adverts` column combine in the `show_adverts` getter on `XF\Entity\User`.
  Nothing in the add-on reads that getter. **Admins are expected to wrap their own advert HTML in
  `<xf:if is="$xf.visitor.show_adverts">`**, so renaming or removing it breaks every forum that
  followed the add-on's instructions, silently.

`canHideAdverts()` and `getShowAdverts()` read `\XF::visitor()`, not `$this`. Called on any other
`User` entity they answer for the current visitor — harmless for the template use above, wrong if
anything ever calls them on another user.

## The preference column defaults to "hide"

`Setup::install()` adds `hide_adverts` as `tinyint` defaulting to `1`, and the `UserOption` entity
mirrors that with `'default' => true`. So a member who is *granted* the permission stops seeing
adverts immediately, without touching their preferences. Changing either default without the other
makes new and existing users behave differently.

The checkbox on the account preferences page comes from the template modification
`hampelHideAdvertsAccountPreferences`, a `preg_replace` whose pattern matches **both** the XF 2.2
(`id="helper_account::activity_privacy_row"`) and XF 2.3 (`template="helper_account"
name="activity_privacy_row"`) macro syntax. Keep both alternatives when editing it; losing one
removes the checkbox on that XF branch with no error. `XF/Pub/Controller/Account.php` saves it,
treating an absent checkbox as `false`.

`Setup::upgrade()` is an empty stub. A schema change needs real upgrade steps, not an edit to
`install()`.

## Commands

Run from the XenForo install root; `cmd.php` resolves the install from its own location, so an
absolute path to it works from this directory too.

```bash
php cmd.php xf-dev:import --addon=Hampel/HideAdverts          # after hand-editing _output/
php cmd.php xf-addon:export Hampel/HideAdverts                # DB -> _output/, this add-on only
php cmd.php xf:addon-upgrade Hampel/HideAdverts               # after a version bump
php cmd.php xf-addon:build-release Hampel/HideAdverts         # release zip into _releases/
```

**Never run `xf-dev:export` without thinking** — its `--addon` defaults to every add-on on the
install. There is no test suite; `TESTING.md` is a placeholder.

Options, phrases, the permission and the template modification live in `_output/` and are edited
through the admin control panel or by hand followed by the import above. The install's `AGENTS.md`
covers the `_output/` workflow, version bumps and releases; the `xenforo-addon-release` skill, where
available, carries the release order.

## Packaging

**Dev-only root files are kept out of two places by two separate mechanisms**, and adding a new one
means updating both:

- **The XenForo release zip** — `build.json`'s `rm -fv` line deletes `TESTING.md`, `CLAUDE.md` and
  `CLAUDE.local.md`. It must run before the `mv` that promotes every remaining root `*.md` to the
  zip root, or it matches nothing and the file ships.
- **`git archive`** (GitHub's "Download ZIP") — `export-ignore` entries in `.gitattributes`.

Machine-specific notes go in `CLAUDE.local.md`, which is gitignored. `.gitattributes` also forces LF
on commit and checkout, because a Windows git client can write CRLF into the working tree.
