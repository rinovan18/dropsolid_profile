# Dropsolid Rocketship Profile

Many thanks to Varbase for providing a large amount of the code and way of
doing things. If you like parts of this profile, chances are it's those parts
so give them a look: https://www.drupal.org/project/varbase

- Dropsolid Rocketship installer
- has option to select extra languages during install
    - can also be done with drush, add following to your drush si command
    - select your default language with the `--locale=LANG` option
    - `dropsolid_rocketship_profile_multilingual_configuration.enable_multilingual=1`
    - `dropsolid_rocketship_profile_multilingual_configuration.multilingual_languages.LANG=LANG`
    for every extra language
- has option to enable some of our own features during install
    - can also be done with drush, add following to your drush si command
    - `dropsolid_rocketship_profile_extra_components.MODULE=MODULE`
- has option to select the theme to use, default is bartik
    - can also be done with drush, add the following
    - `dropsolid_rocketship_profile_extra_components.theme=THEME`
- creates admin and webadmin roles and users
- sets up permissions for webadmin
    - protects superadmin role from editing by webadmin
    - see config/install webadmin.yml for all permissions
- has extra permissions related modules:
    - userprotect
    - roleassign
    - block_content_permissions
    - taxonomy_access_fix
- has config ignore and splits for each environment ready to go. Don't forget
 to run "drush d-set" after installation to set it up AND that the split 
 folders exist.

When multiple languages are selected, sets language switcher block in header_top
When multiple languages are selected, makes language selection visible for 
ONLY menu links. Other content uses the "You are currently doing X in 
language Y" and follows selected interface language. See rocketship_core.

There is a generic page variant for all nodes set up so all nodes are treated
 the same, with the same overall template. Make sure this variant is always 
 last in the list of variants, as it has no requirements to get picked. When 
 needed, create new variants for new content types.

There is also a frontpage variant. Whatever node is set as the frontpage, 
will use that variant. Make sure this one is always first in the list of 
variants so content-type selections don't win out.
