I want to create a custom management system in wordpress with PHP/HTML code with below details.

- LOGIN PAGE 
- ROLE BASED ACCESS

Role 1. Main Admin - with access to user passwords as well
- CRUD Main Admin   	- UI Done
- CRUD Admin		    - UI Done
- CRUD Employee		    - UI Done
- CRUD CorpAccount	    - UI Done
- Assign Employee to CorpAccount - UI Done
- Shift Management      - UI Done
- View Employees Shift History 
- Approve/Reject Requests
- History of Requests
- CRUD Message
- messages list - can mark them as read
- Employee salary lists(1-15th of month, 16th to end of month) + hours worked + hourly wage

Role 2. Admin
- CRUD Employee		    - UI Done
- Assign Employee to CorpAccount - UI Done
- Shift Management      - UI Done
- View Employees Shift History
- Approve/Reject Requests
- History of Requests
- CRUD Message
- messages list - can mark them as read

Role 3. Employee
- view employee details
- Login Shift/ LogOut Shift -- can do this 30 minutes before or after shift time only. and shows shift hours/timing.
- Shift History 	    - UI Done
- Early/late Login/logout Request - UI Done
- history of requests
- salary history
- messages list - can mark them as read
- send msg to admin/mainadmin only

Role 4. CorporateAccount
- view their details
- List of employees under them - show only these details Name, CNIC, CharacterCertificateNo, CharacterCertificateExpiry, terminationdate
- send msg to admin/main admin
- messages list - can mark them as read

Now Ill be sharing my database structure with table names and fields in the table
1. USERS: username, password, role
2. MAIN_ADMIN: username, name, email, father_name, contact_num, emergency_cno, ref1_name, ref1_cno, ref2_name, ref2_cno
3. ADMIN: username, name, email, father_name, contact_num, emergency_cno, ref1_name, ref1_cno, ref2_name, ref2_cno, position
4. EMPLOYEE: username, name, email, father_name, contact_num, emergency_cno, ref1_name, ref1_cno, ref2_name, ref2_cno, joining_date,  wage_type(hourly/monthly), basic_wage, increment_date, increment_percentage, updated_wage, corp_team, position, Cnic_no, Cnic_pdf,  Character_cert_no, char_cert_exp, char_cert_pdf, Emp_letter_pdf, termination_date
5. INCREMENT_HISTORY: username, increment_date, basic_wage, updated_wage, increment_percentage
6. CORP_ACC: username, company_name, name, email, phone_no, address, website
7. SHIFT_HISTORY: username, date, actual_login_time, actual_logout_time, actual_hours, actual_mins, counted_login_time, counted_logout_time, counted_hours, counted_mins
8. REQUESTS: username, request, date, timeallowed, approved, admin_username(whoever admin allows)
9. EMP_SALARY: username, month, hours, wage, status(paid/notpaid/partiallypaid), bonus, total_pay, half_pay_1, half_pay_2, tax
10. MSG_HISTORY: username_sender, username_reciever, time, message, mark_as_read
11. SHIFT_MANAGEMENT: emp_username, date, shift_start_time, shift_end_time, corp_acc_username
12. EMP_CORP_ASSIGN: id, username_emp, username_corp_acc

______
