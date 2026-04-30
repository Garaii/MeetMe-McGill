MeetMe@McGill README

AI usage + 30% outsourced work:
- Error codes and error messages: figuring out what each error code number meant and what kind of message users should expect
- Adapted some lines of code from in-class code demos
- add the ownership verification checks before DB operations (for ex: verifying a slot belongs to the owner before deleting it in delete_slot.php), 
it also suggested the try/catch block in db.php for better error handling and helped add the missing invited user check in 
submit_group_availability.php to prevent uninvited users from accessing group meetings.

URL to the website: https://winter2026-comp307-group20.cs.mcgill.ca/

Project members:
- Mazen Asali (261133621): worked on the PHP/SQLite backend of MeetMe@McGill, setting up the database connection, 
creating and updating the schema, and getting and sending JSON requests to and from the browser end.
Helped a bit with front-end while testing including recurring office hours page and booking link. 

- Sarah Abou-Zahr (261073751): built the PHP backend for MeetMe@McGill, including authentication, 
session management, role-based access control all three booking types (meeting requests, group meetings, 
and recurring office hours), and mailto notifications throughout. I created reusable utility functions 
shared across the whole project.
Also contributed to the frontend by writing CSS to style and improve the UI of several pages.

- Ikram Gara (261181806): Built the student and owner dashboards in React,  implemented all three booking types 
on the frontend, single slots, recurring office hours, and group meetings. Managed in the frontend slot management 
features including visibility toggling, deletion with :mailto notifications, meeting request accept/decline, slots 
are grouped by category for clarity. Handled state management, and general CSS styling. Styled with a clean minimal 
CSS using McGill red as the accent.


