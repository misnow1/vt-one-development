# The vtONE Website Repo

This repository keeps track of the plugins and theme for the vtONE website

## Plugins

    wp/wp-content/plugins/  -- the main plugins directory
      landing-page/         -- redirect the user to a landing page URL in a sane way
      media-list/           -- adds post metadata for media files and a short tag to list them
      prayer-schedule/      -- allows easy scheduling of people for time periods (like in a prayer room)
      unique-downloader/    -- provides a download code experience for users
      vtone-tabs/           -- provides short tags and menu hooks to render pages with jQuery tabs (required by the theme)

## Theme

At one point in time, the theme was based on Modularity Lite by [Graph Paper Press](http://graphpaperpress.com)
but it bears little resemblance to it now. This is mentioned for posterity and because you
should never forget where you came from.

    wp/wp-content/themes/      -- the main themes directory
      vtone-modularity-lite-3  -- the vtONE website theme (2014 version)

## Ansible Cookbooks

The included Ansible cookbooks will take a machine from bare metal to having
a functional version of the website with a database dump from a while ago.

__NOTE:__ These scripts will destroy a functioning Wordpress database if it is present!
Make a backup before running them if the machine is not shiny and new!

    ansible/           -- home of all things ansible
      wordpress/       -- stuff specific to wordpress installation
        site.yml       -- within this file are the various roles that will be applied
        production     -- the inventory file for production servers (see below)
        group_vars/    -- variables you care about
          production   -- variables that affect the wordpress installation
        roles/         -- the various roles that will be

I've been using the "production" inventory. Here's an example inventory file for the vtONE droplet:

`ansible/wordpress/production`
    # group defitions

    [production]
    vtonedroplet.vt-one.org

    # end of groups

And here's an example of the variables that determine how a Wordpress instance will be deployed:

    vhosts:
      vtonedroplet.vt-one.org:
        port: 80
        ssl_port: 443
        owner: wordpress
        group: apache
        default: yes
        restore_db:
          db_name: wordpress
          db_user: wordpress
          db_password: ********
          sqlfile: vtoneorg_db-20140513-084137.sql.gz
          tmpdir: /tmp
        wordpress:
          url: vtonedroplet.vt-one.org
          installdir: wp
          version: latest
          auto_up_disable: false
          core_update_level: true
          db_name: wordpress
          db_user: wordpress
          db_password: ********

    wordpress_plugins:
      - url: vtonedroplet.vt-one.org
        installdir: wp
        plugins:
          - url: http://downloads.wordpress.org/plugin
            file: contact-form-7.3.8.1.zip
          - url: http://downloads.wordpress.org/plugin
            file: contact-form-7-to-database-extension.2.7.1.zip
          - url: http://downloads.wordpress.org/plugin
            file: google-analytics-for-wordpress.4.3.5.zip
          - url: http://downloads.wordpress.org/plugin
            file: jquery-colorbox.zip
          - url: http://downloads.wordpress.org/plugin
            file: nextgen-gallery.zip
          - url: http://downloads.wordpress.org/plugin
            file: twitter-widget-pro.2.6.0.zip
          - url: http://downloads.wordpress.org/plugin
            file: w3-total-cache.0.9.4.zip
          - url: http://downloads.wordpress.org/plugin
            file: wordpress-seo.1.5.3.2.zip
          - url: http://downloads.wordpress.org/plugin
            file: wp-video-lightbox.zip

__TODO:__ Automatically deploy the plugins and theme referenced in this git repository

To do the needful, run `ansible-playbook` like this:

    cd ansible/wordpress
    ansible-playbook -i production site.yml --user root -k

When prompted, enter the root password for the server being deployed to.
Then, sit back and watch the magic happen. You may need to visit
`https://SITE_URL/wp/wp-admin` and upgrade the database.

You'll also need to check out this repo and make it work in the plugins and theme directory.
I'm sorry. I haven't gotten to it yet.
