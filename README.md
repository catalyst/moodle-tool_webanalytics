![GitHub Workflow Status (branch)](https://img.shields.io/github/workflow/status/catalyst/moodle-tool_webanalytics/ci/master)


# Web Analytics

A Moodle admin tool adding Web Analytics to your Moodle site.

## Install instructions
1. Copy the code to admin/tool/webanalytics directory of your Moodle instance
2. Visit the notifications page and install the plugin
3. Visit a plugin configuration page and add instances of required Web Analytics Tool

## The plugin features the following options
- multiple unlimited instances of different types of analytics in one Moodle site
- exclude tracking of admin users

## Supported Web Analytics Tools 

The plugin currently supports following analytics tools.

### Matomo (formerly Piwik)
- Set the Site ID
- Choose whether you want image fallback tracking
- Enter the URL to your Matomo install excluding http/https and trailing slashes
- Choose whether you want to track admins (not recommended)
- Choose whether you want to send Clean URLs (recommended): Matomo will aggregate Page Titles and show a nice waterfall cascade of all sites, 
- Set alternative piwik.js URL for any purpose
including categories and action types
- Optional tracking for User ID
- User ID could be id or username

#### Matomo auto-provisioning
- Set the watool_matomo global config settings 'apitoken' and 'apiurl' to enable auto provisioning. These can also be set in config.php e.g:
    - `$CFG->forced_plugin_settings['watool_matomo']['siteurl'] = 'https://matomo.org';` The url of the matomo server.
    - `$CFG->forced_plugin_settings['watool_matomo']['apitoken'] = 'xxxx';` The token to allow use of the API at the server.
- An attempt to auto provision will be made the first time a page is loaded when the API config above is set.
- A successfully auto-provisioned site will have an entry in the management page `admin/tool/webanalytics/manage.php` prefixed with 'auto-provisioned'.
- If the Moodle site url changes after an auto provisioned site has been stored, the next page load will attempt to update the instance on the configured Matomo instance with the new url.
- If autoprovisioning failed, the instance will be set with the name 'auto-provisioned:FAILED' to stop continuing attempts per page load. Delete the instance to attempt an autoprovision again.
- You can register with Matomo on manually creating/updating an instance at `admin/tool/webanalytics/manage.php` also. When submitting the form, if the `siteurl` and `apitoken` fields are set and the instance is being created, an attempt to register with the API will be made and the site id will be stored against the instance. If editing an instance, on save the plugin will check the API to see if the  Moodle DNS has changed since the instance was last saved and if so it will attempt to register the current URL against that site id in Matomo.

### Google Universal Analytics
- Plugin modifies the page speed sample to have 50% of your visitors samples for page speed instead of 1% making it much more useful
- Set your Google tracking ID
- Choose whether you want to track admins (not recommended)
- Choose whether you want to send Clean URLs (not recommended): Google analytics will no longer be able to use overlays and linking back to your Moodle site
- Choose whether you want to send User ID reference
- Choose whether you want to anonymize the IP address of the hit sent to Google Analytics

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

