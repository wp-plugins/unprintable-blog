=== Unprintable Blog ===
Contributors: greencp
Donate link: http://www.greencomputingportal.de
Tags: Unprintable Blog, green, paper waste, print, printer, pdf, mpdf, prevent printing, wp-mpdf
Requires at least: 2.8
Tested up to: 3.0.3
Stable tag: 1.0

Prevent visitors from printing out your blog posts. Posts can be downloaded as unprintable PDFs. Reduce paper waste - make your blog unprintable!

== Description ==

Unnecessary printing not only means unnecessary cost of paper and inks, but also avoidable environmental impact on producing and shipping these supplies. Reducing printing can make a small but a significant impact. Take action to help reduce paper waste - make your blog "unprintable". 

"Unprintable Blog" inserts code in every page that prevents that page from being printed out via the browsers print function. Instead only blank pages are printed, optionally containing a note to not print the page but to use the pdf download instead in order to save the environment. As alternative to printing, Wordpress posts can be downloaded as automatically generated (unprintable) pdf files with optional syntax highlighting.

The plugin is based on the wp-mpdf plugin by Florian Krauthan. It uses the PHP libs Geshi and Mpdf and parts from the Wordpress plugins wp_syntax and contuttoPDF.

== Frequently Asked Questions ==

== Upgrade Notice ==

== Screenshots ==

1. Prevent users from printing your blog posts

== Changelog ==  

= 1.0 =
* Initial release

== Installation ==

1. Upload the whole plugin folder to your /wp-content/plugins/ folder.
2. Set write permission (777) to the plugin dir folders => unprintable-blog/cache AND unprintable-blog/mdpf/graph_cache
3. Go to the plugins page and activate the plugin.
4. Add to your template "&lt;?php if(function&#95;exists('gcp_pdf&#95;pdfbutton')) gcp_pdf&#95;pdfbutton(); ?&gt;" as a small button or "&lt;?php if(function&#95;exists('gcp_pdf&#95;pdfbutton')) gcp_pdf&#95;pdfbutton('my link', 'my login text'); ?&gt;" as a textlink. The second text specifies the text which should displayed if you have checked "needs login" and a user isn't loggend in.
 (if you wish to open the pdf print in a new tab you may pass "true" for the first parameter)

== License ==

"Unprintable Blog" is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

"Unprintable Blog" is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with "Unprintable Blog". If not, see <http://www.gnu.org/licenses/>.
