<a href="https://travis-ci.org/catalyst/moodle-tool_webanalytics">
<img src="https://travis-ci.org/catalyst/moodle-tool_webanalytics.svg?branch=master">
</a>

# Web Analytics

A Moodle admin tool adding Web Analytics to your Moodle site.

## Install instructions
1. Copy the code to admin/tool/webanalytics directory of your Moodle instance
2. Visit the notifications page and install the plugin
3. Visit a plugin configuration page and add instances of required Web Analytics Tool

## The plugin features the following options
- multiple unlimited instances of different types of analytics in one Moodle site
- tracking code can go to Header, Top of body or Footer of any page
- exclude tracking of admin users

## Supported Web Analytics Tools 

The plugin currently supports following analytics tools.

### Matomo (formerly Piwik)
- Set the Site ID
- Choose whether you want image fallback tracking
- Enter the URL to your Matomo install excluding http/https and trailing slashes
- Choose whether you want to track admins (not recommended)
- Choose whether you want to send Clean URLs (recommended): Matomo will aggregate Page Titles and show a nice waterfall cascade of all sites, including categories and action types

### Google Universal Analytics
- Plugin modifies the page speed sample to have 50% of your visitors samples for page speed instead of 1% making it much more useful
- Set your Google tracking ID
- Choose whether you want to track admins (not recommended)
- Choose whether you want to send Clean URLs (not recommended): Google analytics will no longer be able to use overlays and linking back to your Moodle site
- Choose whether you want to send User ID reference

### Google Tag Manager
- Tag Manager adds Analytics page view tags, AdWords Conversion Tracking tags, and others in the Tag Manager user interface.
- Set your Tag Manager Container ID tracking ID
- Choose whether you want to track admins

### Google Legacy Analytics (soon deprecated by Google)
- Plugin modifies the page speed sample to have 50% of your visitors samples for page speed instead of 1% making it much more useful
- Set your Google tracking ID
- Choose whether you want to track admins (not recommended)
- Choose whether you want to send Clean URLs (not recommended): Google analytics will no longer be able to use overlays and linking back to your Moodle site

## Developer notes
All Web Analytics tools has implemented as Moodle subplugins. If you require to support a new web analytics tool, then add a new subplugin to "tool" folder. See existing subplugins as a code example.

# Crafted by Catalyst IT

This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

![Catalyst IT](/pix/catalyst-logo.png?raw=true)

# Contributing and Support

Issues, and pull requests using github are welcome and encouraged! 

https://github.com/catalyst/moodle-tool_webanalytics/issues

If you would like commercial support or would like to sponsor additional improvements
to this plugin please contact us:

https://www.catalyst-au.net/contact-us

