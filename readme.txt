=== PCRecruiter Extensions ===
Contributors: Main Sequence Technology, Inc.
Tags: Recruiting, Staffing, Applicant Tracking
Requires at least: 3.0
Tested up to: 6.6.0
Stable tag: trunk

This plugin generates an iframe and JavaScript for the PCRecruiter Job Board via a simple shortcode. It does not interact with the WordPress database or software. The plugin also has an optional RSS download feature for PCRecruiter dynamic job feeds. This plugin requires your web server to be running PHP 5.4 or higher.


== Job Board Installation ==

IMPORTANT!!! You do NOT need to touch the SETTINGS panel for standard PCRecruiter Job Board installations.

1.  Click 'Add New' from the 'Plugins' menu in your WordPress admin panel.
2.  Use the 'Upload Plugin' option and browse to the PCRecruiter-Extensions.zip file.
3.  Activate the plugin.
4.  Edit the page where you want to display the PCRecruiter content and paste in the following:

    [PCRecruiter link=""]

    Place the shortcode content provided to you by Main Sequence between the quotes. If the URL does not begin with 'http', the plugin will assume you intend to load a URL from the PCRecruiter ASP hosting system and will prepend the correct URL.

Optional parameters:

*   initialheight="" (defaults to 640 pixels if omitted)
*   background="" (defaults to transparent if omitted)
*   form="" (insert the 15-digit ID of a custom form)
*   analytics="" (set to on if integrating Google Analytics with PCR)

== XML Feed Setup ==

From the Settings > PCRecruiter Extensions panel, you may configure the plugin to duplicate a dynamic RSS job feed from PCRecruiter to a static copy on your WordPress server. This may be advantageous for running RSS-based display widgets, feed-based distribution services, or other functions that require a static XML/RSS feed.

IMPORTANT!!! You do NOT need to touch the Settings panel to use this plugin for standard job board setups. Enabling the feed without correct settings values may cause errors or break your website. Please contact a Main Sequence Technology support representative for the settings required for use of this function.

* Job Feed Enabled: Checking this box activates the feed. Unchecking will deactivate and delete the XML file.

* Frequency of Update: The feed can be set to refresh daily, hourly, or twice daily.

* PCR SessionID: This encoded string identifies which PCRecruiter database to load the content from. Your support contact will provide the appropriate value for this field.

* Standard Fields: The feed will contain the job link, date entered, title, and description. To include additional fields, enter them in comma-separated form in this box. The list of values that are accepted can be found in the API documentation at https://www.pcrecruiter.net/apidocs_v2/#!/positions/GetPosition_get_0.

* Custom Fields: Custom fields can be included in comma-separated form as well.

* Query: By default, the feed will contain jobs that are set to Available/Open status, have the Show On Web field set to "Show", and have a Number of Openings that is 1 or greater. Contact Main Sequence Technology for assistance if alternate queries are needed.

* Mode: This setting dictates whether the job links in the feed point to the job details page or to the apply screen for the job. The 'apply' mode would be useful in scenarios where the candidate will have already seen the description and should be directed straight to the first step of self-entry.
