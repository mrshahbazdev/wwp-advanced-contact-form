Advanced Custom Contact Form
Contributors: Mr Shahbaz
Version: 2.2
Requires at least: 5.0
Tested up to: 6.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful, customizable, and easy-to-use contact form plugin for WordPress with AJAX submission, a drag-and-drop form builder, and advanced email options.

Description
Advanced Custom Contact Form is the complete solution for creating beautiful and functional contact forms on your WordPress website. Say goodbye to page reloads with our smooth AJAX submission. Our intuitive form builder allows you to create any kind of form by simply dragging and dropping fields.

With advanced features like file uploads, custom email templates, and styling options, you have full control over your forms' appearance and functionality.

Key Features
AJAX Powered Submission: Forms submit without reloading the page for a smooth user experience.

Drag & Drop Form Builder: Easily create and manage forms with an intuitive admin interface.

Multiple Field Types: Includes Text, Email, Phone, Textarea, Dropdown, Checkbox, Radio Buttons, and File Uploads.

Custom Email Templates: Design multiple email templates and assign them to different forms.

File Attachments: Allow users to upload files, which are sent as attachments with the notification email.

Database Submissions: All form entries are saved in the database for you to view anytime.

Custom Styling: Add your own custom CSS directly from the settings page to match your theme.

Easy to Use: Simply add the form to any page or post using a shortcode.

Developer Friendly: Built with a clean and organized code structure.

Installation
Upload the advanced-custom-contact-form folder to the /wp-content/plugins/ directory.

Activate the plugin through the 'Plugins' menu in your WordPress dashboard.

Go to Contact Forms > Settings to configure the basic options.

Go to Contact Forms > Add New to create your first form!

How to Use
Create a Form:

Navigate to Contact Forms > Add New in your WordPress admin panel.

Give your form a title.

Use the "Available Fields" panel on the right to add fields to your form.

Click on any added field to configure its settings (label, placeholder, required, etc.).

Configure form settings like the Submit Button Text and Email Template.

Click Save Form.

Display the Form:

After saving, go to Contact Forms > All Forms.

Find your form in the list and copy its shortcode. It will look like this: [advanced_contact_form id="1"].

Paste this shortcode into any page, post, or widget where you want the form to appear.

Frequently Asked Questions (FAQ)
Q: I am receiving the "Thank you" message, but I am not getting the notification emails.

A: This is a very common WordPress issue, often caused by web hosting server configurations that block or filter emails sent by PHP. The best and most reliable solution is to use an SMTP plugin to send emails through a dedicated email service (like Gmail, SendGrid, etc.).

We highly recommend installing the WP Mail SMTP plugin. Once configured, it will handle all email sending for your entire WordPress site, ensuring reliable delivery.

Q: Can I change the design of the form?

A: Yes! You have two options:

For minor changes, go to Contact Forms > Settings and add your own styles in the Custom CSS box.

For major changes, you can copy the frontend.css file from the plugin's assets/css/ directory into your theme's folder and modify it there.

Changelog
= 2.2 =

Feature: Added support for File Uploads.

Feature: Added custom "From" email address setting.

Fix: Resolved issues with database table creation on multisite installs.

Tweak: Improved email headers for better deliverability.

= 2.0 =

Initial release.