# Real Estate System

Project Documentation

Prepared using the approved report structure provided in `Event Management System - Project Documentation.pdf`

Prepared For:
- Board review and academic/project submission

Prepared By:
- Ali Ayaz Ahmad

Technology Stack:
- Java
- JSP / Servlets
- MySQL
- HTML / CSS / JavaScript
- Apache Tomcat

Repository Path:
- `C:\Users\aliay\OneDrive\Documents\Playground\real-estate-system\board-approved-java-app`

## Table of Contents

1. Introduction/Objectives
2. System Analysis
3. Project Planning
4. Software Requirement Specifications (SRS)
5. Software Engineering Paradigm Applied
6. System Design
7. Modularisation Details
8. Data Integrity and Constraints
9. Database Design
10. User Interface Design
11. Test Cases
12. Coding
13. Testing
14. System Security Measures
15. Cost Estimation of the Project
16. Reports
17. Future Scope and Enhancements
18. Bibliography
19. Appendices
20. Glossary

## 1. Introduction/Objectives

The Real Estate System is a web-based application designed to digitalize the workflow of property discovery, listing management, booking requests, payment capture, and administrative oversight. The system supports three main roles: customer, agent, and admin. Customers can browse approved listings and request visits, agents can manage property listings, and administrators can review inventory, monitor bookings, and analyze reports.

The main objectives of the project are:
- To provide a centralized platform for managing real-estate operations.
- To reduce manual paperwork and fragmented communication between stakeholders.
- To support role-based access control for secure and organized operations.
- To allow customers to search properties and request visits online.
- To allow agents and admins to manage inventory, bookings, and payments efficiently.
- To provide an extensible foundation for future reporting and deployment enhancements.

## 2. System Analysis

### Identification of Need

Traditional real-estate workflows often rely on calls, spreadsheets, paper registers, and unstructured messaging. This leads to delays, duplicate work, poor visibility of property status, and weak reporting. A digital system is needed to:
- Maintain a centralized property inventory.
- Track booking requests and visit schedules.
- Preserve customer and agent account data securely.
- Provide management insight into approvals, bookings, and revenue.

### Preliminary Investigation

An initial study of the domain showed that most property systems require the following:
- Secure user registration and login.
- Clear separation of customer, agent, and admin responsibilities.
- Search and filtering for property discovery.
- Booking and payment tracking.
- Administrative dashboards and reports.

The approved synopsis aligned with these findings and defined the official implementation direction as a Java/JSP/Servlet/MySQL web application.

### Feasibility Study

- Technical Feasibility:
  The system is feasible using Java Servlets, JSP, MySQL, Maven, and Tomcat. The current codebase has already been structured into models, DAOs, utilities, servlet controllers, JSP views, SQL scripts, and static assets.
- Economic Feasibility:
  The project uses mostly free and open-source technologies. Development cost is primarily engineering effort rather than software licensing.
- Operational Feasibility:
  The platform simplifies listing approval, visit booking, and reporting for stakeholders who would otherwise manage these processes manually.

## 3. Project Planning

The project was planned as a structured MVP aligned with the approved synopsis. The work was divided into the following phases:
- Requirements alignment with the approved product synopsis.
- Database schema design and relationship mapping.
- Authentication and role-management setup.
- Property module implementation.
- Booking and payment workflow implementation.
- Admin dashboard and reporting implementation.
- Build, packaging, and deployment preparation.

### Project Scheduling

The logical work order is:
- Phase 1: Requirement study and approved report alignment.
- Phase 2: Database schema and backend utility setup.
- Phase 3: Authentication, dashboard, and property catalogue.
- Phase 4: Booking and payment modules.
- Phase 5: Reporting, documentation, and deployment packaging.

Suggested insertions for the formal submission:
- PERT chart placeholder
- Gantt chart placeholder

## 4. Software Requirement Specifications (SRS)

### Functional Requirements

- User registration and login.
- Role-based access for customer, agent, and admin.
- Profile management.
- Property creation, editing, deletion, and approval workflow.
- Property search and filtering by city, type, purpose, and price.
- Booking request submission and status management.
- Payment recording and receipt generation.
- Administrative dashboard with operational summaries.
- Reports for property status, booking status, revenue, and city-wise distribution.

### Non-Functional Requirements

- Usability through a responsive web interface.
- Maintainability through modular Java code and DAO separation.
- Security through session checks, role checks, and request validation.
- Reliability through relational constraints and controlled workflows.
- Portability through WAR packaging for Tomcat deployment.

## 5. Software Engineering Paradigm Applied

The project follows an iterative and modular development approach inspired by Agile practices. Work was organized in small deliverable layers:
- database and utilities
- controllers and business flow
- JSP views and user interaction
- reporting and documentation

The implementation also follows a layered design:
- presentation layer through JSP
- request handling through Servlets
- data access through DAO classes
- persistence through MySQL

## 6. System Design

The system is designed as a server-rendered Java web application. HTTP requests are handled by Servlets, business and data queries are delegated to DAO classes, and dynamic pages are rendered by JSP views.

### Design Overview

- Client Layer:
  Browser-based user interface built with JSP, CSS, and JavaScript.
- Controller Layer:
  Servlets such as `AuthServlet`, `PropertyServlet`, `BookingServlet`, `PaymentServlet`, `AdminServlet`, and `ReportsServlet`.
- Data Layer:
  DAO classes such as `UserDao`, `PropertyDao`, `BookingDao`, and `PaymentDao`.
- Persistence Layer:
  MySQL database with normalized tables and indexed foreign keys.

### Diagram Placeholders

Suggested insertions in the final academic version:
- Data Flow Diagram (DFD)
- Entity Relationship Diagram (ERD)
- Use Case Diagram
- Sequence Diagram
- Class Diagram
- Activity Diagram

## 7. Modularisation Details

The project is modularized into the following core components:

- Authentication Module
  Handles login, registration, logout, session state, and role routing.
- Dashboard Module
  Shows role-aware summaries for customers, agents, and admins.
- Property Module
  Manages listing creation, editing, deletion, approval state, and search filters.
- Booking Module
  Handles visit requests, booking status updates, and customer tracking.
- Payment Module
  Records payment details, invoice numbers, and receipt pages.
- Reporting Module
  Displays analytics for admin users, including status charts and payment trends.

### Source Structure

- Java source: `src/main/java/com/realestate/app`
- JSP views: `src/main/webapp/WEB-INF/views`
- Static assets: `src/main/webapp/assets`
- SQL schema: `sql/schema.sql`
- Build configuration: `pom.xml`

## 8. Data Integrity and Constraints

The system enforces data integrity through:
- Primary keys on all core tables.
- Foreign key relationships between users, properties, bookings, and payments.
- Unique constraints on email and invoice number.
- Unique payment-to-booking relationship through `payments.booking_id`.
- Indexed access paths for status and ownership queries.

### Validation Rules

- Email addresses must be valid.
- Passwords must satisfy minimum strength checks.
- Numeric values such as price and amount must be positive.
- Booking dates and visit dates follow workflow constraints.
- Role values and status values are restricted by controlled application logic.

## 9. Database Design

The database uses the schema defined in `sql/schema.sql` and currently includes the following primary tables:

- `users`
  Stores name, email, phone, password hash, role, and timestamps.
- `properties`
  Stores ownership, title, location, price, type, purpose, area, image, description, and approval status.
- `bookings`
  Stores customer-to-property booking requests, visit date, message, and workflow status.
- `payments`
  Stores booking-linked payments, method, transaction reference, amount, invoice number, and receipt notes.

### Database Operations Covered

- Database creation
- User creation
- Property insertion and update
- Booking creation and status changes
- Payment creation and reporting queries

## 10. User Interface Design

The user interface is designed as a responsive browser-based experience with role-aware navigation and clear workflow actions.

### Main Screens

- Home page
- Login page
- Registration page
- Customer dashboard
- Agent dashboard
- Profile page
- Property catalogue
- Property form
- Booking form
- Bookings list
- Payment form
- Payment receipt
- Admin dashboard
- Reports dashboard

### UI Principles

- Clean role-based navigation.
- Consistent form styling and action buttons.
- Status badges for approvals, bookings, and payments.
- Responsive layouts for desktop and mobile use.

Suggested insertions in the final submission:
- screenshot of home page
- screenshot of login/registration
- screenshot of property listing page
- screenshot of booking page
- screenshot of payment receipt
- screenshot of admin and reports pages

## 11. Test Cases

Representative test cases for the system include:

- User Registration
  Verify that a new customer or agent can register with valid input and that duplicate email addresses are rejected.
- Login Validation
  Verify that valid credentials create a session and invalid credentials show an error.
- Property Submission
  Verify that an agent can create a property and that the property appears with pending status.
- Property Approval
  Verify that an admin can change a property from pending to approved.
- Booking Request
  Verify that a customer can request a visit only for approved properties.
- Payment Recording
  Verify that a customer can record payment for a confirmed booking and generate a receipt.
- Reports Access
  Verify that only admins can access the reports pages.

## 12. Coding

The implementation follows modular coding practices and separates concerns across multiple packages and files.

### Coding Highlights

- DAO-based database access for reusable query logic.
- Base servlet utilities for rendering, redirects, login checks, and role checks.
- Utility classes for password hashing, flash messaging, uploads, sessions, and formatting.
- JSP-based frontend views with shared header and footer partials.
- Maven-based build process that packages the application as a deployable WAR.

### Coding Quality Focus

- organized file structure
- readable controllers and models
- validation before persistence
- clear role-aware request routing
- reusability of utility components

## 13. Testing

Testing for this project includes both build validation and workflow-oriented manual verification.

### Testing Techniques and Strategy

- build/package verification through Maven
- manual functional walkthroughs for authentication, property flow, bookings, payments, and admin reports
- database-level verification of tables and relationships

### Current Status

- `mvn clean package` completed successfully and generated `target/board-approved-real-estate.war`
- source-level review of routes, DAOs, views, schema, and utilities has been completed
- final live browser validation depends on local Tomcat and database runtime setup

### Debugging and Code Improvement

The implementation was improved through repeated refinement of:
- servlet routing
- DAO queries
- report pages
- form handling
- security utilities

## 14. System Security Measures

Security measures in the project include:

- Password Hashing
  Passwords are not stored as plain text. The utility in `PasswordUtil.java` uses salted SHA-256 hashing.
- Session-Based Authentication
  Logged-in users are stored in session state and checked before protected routes are served.
- Role-Based Access Control
  Customers, agents, and admins are restricted to their permitted operations.
- CSRF Token Handling
  Forms are issued security tokens through the CSRF utility and validated on submission.
- Input Validation
  Email, phone, amounts, and date values are validated before processing.
- Restricted Workflow Transitions
  Only approved properties can be booked, and payment is tied to the booking workflow.

## 15. Cost Estimation of the Project

This project primarily uses open-source or freely available technologies, so licensing cost is minimal.

### Estimated Development Cost

- Developer effort:
  Approximately 180 to 220 hours for a polished MVP aligned with the approved synopsis.
- If valued as professional engineering work:
  Cost depends on hourly rate and deployment environment.

### Infrastructure Cost

- Development tools:
  Java, Maven, MySQL, and Tomcat can be used at no licensing cost for development.
- Hosting:
  Local hosting is low-cost. Cloud hosting would add server, database, and storage charges.

### Maintenance Cost

Potential future maintenance includes:
- bug fixing
- report enhancement
- security updates
- deployment monitoring
- additional user-facing features

## 16. Reports

The system contains an admin reporting area designed to support managerial review.

### Available Report Views

- property status counts
- booking status counts
- top cities by number of listings
- recent payment activity
- total revenue summary
- customer-facing payment receipt view

### Sample Layouts for Formal Submission

Suggested report screenshots:
- admin dashboard overview
- reports dashboard
- revenue/payment summary
- receipt page

## 17. Future Scope and Enhancements

The project can be extended in several useful directions:
- online payment gateway integration
- email and SMS notifications
- property image galleries and multiple uploads
- advanced search and saved filters
- document upload for ownership verification
- appointment reminders and calendars
- exportable reports in PDF/Excel
- production deployment with CI/CD

## 18. Bibliography

1. Oracle. Java Documentation. https://docs.oracle.com/
2. Apache Tomcat Documentation. https://tomcat.apache.org/
3. Apache Maven Documentation. https://maven.apache.org/
4. MySQL Documentation. https://dev.mysql.com/doc/
5. OWASP Foundation. OWASP Top Ten. https://owasp.org/www-project-top-ten/
6. Jakarta Servlet / Javax Servlet API Documentation. https://jakarta.ee/
7. HTML Living Standard. https://html.spec.whatwg.org/
8. MDN Web Docs. https://developer.mozilla.org/

## 19. Appendices

### Appendix A: Implemented Source Modules

- `AuthServlet.java`
- `DashboardServlet.java`
- `PropertyServlet.java`
- `BookingServlet.java`
- `PaymentServlet.java`
- `AdminServlet.java`
- `ReportsServlet.java`

### Appendix B: Key JSP Views

- `home.jsp`
- `login.jsp`
- `register.jsp`
- `dashboard.jsp`
- `profile.jsp`
- `properties.jsp`
- `property-form.jsp`
- `booking-form.jsp`
- `bookings.jsp`
- `payment-form.jsp`
- `payment-receipt.jsp`
- `admin.jsp`
- `reports.jsp`

### Appendix C: Build and Deployment Summary

- Build command:
  `mvn clean package`
- WAR output:
  `target/board-approved-real-estate.war`
- Intended deployment:
  Apache Tomcat 8.5+
- Database:
  MySQL / MariaDB compatible schema in `sql/schema.sql`

### Appendix D: Screenshot Checklist

- home page
- registration page
- login page
- property catalogue
- add/edit property page
- booking request page
- payment receipt page
- admin dashboard
- reports dashboard

## 20. Glossary

- JSP:
  Java Server Pages used to render dynamic server-side views.
- Servlet:
  Java class that handles HTTP requests and responses.
- DAO:
  Data Access Object used to isolate database logic.
- WAR:
  Web application archive deployed to a servlet container such as Tomcat.
- RBAC:
  Role-Based Access Control.
- CSRF:
  Cross-Site Request Forgery protection mechanism.
- MVC:
  Model-View-Controller style separation of concerns.
- MVP:
  Minimum Viable Product.
- ORM:
  Object-Relational Mapping, a database access pattern used in many enterprise systems.

---

Note for final submission:
- This report is written to match the structure of the approved sample report while reflecting the actual Real Estate System implementation and approved synopsis stack.
- Diagram images and final UI screenshots can be inserted into the marked sections once the live deployment is finalized.
