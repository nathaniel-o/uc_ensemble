Untouched Cocktails 

untouchedcocktails.com

HTML5, CSS3, Vanilla JS, WordPress, PHP

vision by JRA [copy verb'ge]

development start June 2022


## What Changed

### Home recent posts — exclude site/admin authors (2026-07)

- `templates/home.html` now uses pattern `uc-theme-ui/home-recent-posts` instead of an inline Query Loop.
- New pattern: `patterns/uc-home-recent-posts.php` — same drink-post list/pagination as before, with `query.ucExcludeAuthors` set to `nathaniel`, `juliarotoole25229`, `information43e7b5f30d`.
- `functions.php` filter `query_loop_block_query_vars` → `uc_home_recent_posts_exclude_authors` maps those logins to user IDs and sets `author__not_in`.
- Note: the filter must read `ucExcludeAuthors` from `$block->context['query']` (not parent `namespace`), because WordPress builds the loop query on child blocks like `core/post-template`.
- Theme version bumped to 1.0.3 so theme pattern cache picks up the new file.
- Expectation: with current content, those three users authored all published posts, so the User Content / posts page may show an empty loop until other authors publish.
