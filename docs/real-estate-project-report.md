# REAL ESTATE PROPERTY LISTING & BOOKING SYSTEM

### IGNOU BCA FINAL YEAR PROJECT REPORT (BCSP-064)

---

# TABLE OF CONTENTS

1. Title
2. Introduction
3. Objectives
4. Category of Project
5. Tools & Hardware Requirements
6. System Analysis - UML Diagrams
7. Data Flow Diagram (DFD)
8. Entity Relationship Diagram
9. Data Modeling
10. Modules
11. Project Planning & Timeline
12. Security & Validation
13. Future Scope
14. Limitations
15. Bibliography

---

# 1. TITLE

**Real Estate Property Listing & Booking System**

A web-based enterprise application designed to manage property listings, facilitate booking operations, and provide administrative control using modern web technologies and relational database systems.

---

# 2. INTRODUCTION

## 2.1 Background

The real estate sector plays a vital role in economic development and involves complex operations such as property listing, customer interaction, booking management, and transaction tracking. Traditionally, these operations were carried out manually, leading to inefficiencies, delays, and errors.

With the advancement of digital technologies, there is a growing need for automated systems that can provide real-time access to property information and streamline booking processes.

---

## 2.2 Problem Statement

The traditional real estate management system suffers from several drawbacks:

- Lack of centralized database for properties
- Time-consuming manual booking processes
- Limited accessibility (office hours only)
- High dependency on intermediaries
- Difficulty in managing large datasets
- Poor communication between users and administrators

---

## 2.3 Proposed Solution

The proposed system provides a **web-based platform** that enables:

- Digital property listing and management
- Advanced property search using multiple filters and criteria
- Real-time booking functionality with faster confirmation flow
- Online payment handling and receipt support
- Admin control panel for system monitoring
- Secure user authentication, authorization, and session handling

---

## 2.4 Scope of the System

### In Scope:

- User registration and login
- Property listing and management
- Advanced property search
- Booking system
- Payment processing
- Report generation
- Admin dashboard
- Security and validation controls

### Out of Scope:

- Mobile application
- AI-based recommendations
- Virtual tours

---

# 3. OBJECTIVES

## 3.1 Primary Objectives

- To provide a centralized platform for property management
- To enable 24/7 accessibility from anywhere
- To ensure secure and reliable booking processes
- To improve efficiency and reduce manual effort
- To maintain data security through encryption and authentication
- To ensure data accuracy through proper validation and reliable calculations

---

## 3.2 Secondary Objectives

- To design a scalable and modular system
- To enhance user experience with an intuitive interface
- To maintain high performance and fast database response time
- To ensure data consistency and integrity
- To support future customization and enhancements
- To reduce operational cost through automation

---

# 4. CATEGORY OF PROJECT

This project falls under:

**Web-Based Application Development using Object-Oriented Principles and Relational Database Management Systems (RDBMS)**

---

## 4.1 Technology Stack

| Layer | Technology |
| --- | --- |
| Frontend | JSP, HTML5, DHTML, CSS3, JavaScript |
| Backend | Java Servlets, JSP |
| Database | MySQL 5.7+ (InnoDB) |
| IDE | NetBeans 7.0+ |
| Web Server | Apache Tomcat 6.0+ |

---

## 4.2 System Architecture

The system follows a **4-layer architecture**:

### 1. Presentation Layer

Handles user interaction through JSP, HTML, DHTML, CSS, and JavaScript.

### 2. Business Logic Layer

Processes user requests through Java Servlets and JSP and implements business rules and session handling.

### 3. Data Access Layer

Manages database communication, SQL execution, and transaction handling through JDBC.

### 4. Database Layer

Stores structured data such as customers, agents, properties, bookings, and payments in MySQL.

### System Characteristics

- High availability target for regular use
- Scalable design for large property inventories
- Security through SHA-256, RBAC, and transport protection
- Fast response expectations for core queries
- Manageability through admin dashboards and reports

---

## 4.3 Project File Structure

The implementation is organized into functional files and folders so that presentation, business logic, and database connectivity remain easier to manage.

### Main Project Files

- `index.php` - Home page and public entry point
- `login.php` - User login processing
- `register.php` - User registration processing
- `dashboard.php` - Role-based dashboard
- `profile.php` - User profile page
- `properties.php` - Property listing and search
- `add_property.php` - Add new property
- `edit_property.php` - Edit property details
- `book.php` - Booking request page
- `my_bookings.php` - Booking history page
- `payments.php` - Payment handling
- `payment_receipt.php` - Receipt output page
- `admin_dashboard.php` - Admin monitoring panel
- `reports.php` - Reports and summaries
- `src/` - Java source files for servlet-based implementation structure
- `pom.xml` - Maven build configuration for the Java web application

### Supporting Folders

- `css/` - Stylesheets
- `js/` - JavaScript files
- `php/` - Shared backend helpers and database files
- `uploads/` - Uploaded property images
- `sql/` - Database scripts
- `docs/` - Project report and related documentation

---

# 5. TOOLS & HARDWARE REQUIREMENTS

## 5.1 Hardware Requirements

- Processor: Pentium 2.4 GHz or above
- RAM: 256 MB or above
- Hard Disk: 3 GB or above
- Pen Drive: 2 GB for backup
- Printer: Laser printer

---

## 5.2 Software Requirements

- Operating System: Windows XP/Vista/7/8/10 or Linux
- Web Server: Apache Tomcat 6.0+
- Database: MySQL 5.5+
- IDE: NetBeans 7.0+
- JDK: Java SE 6 or above
- Browser: Chrome, Firefox, Safari, Edge
- Frontend: HTML5, CSS3, JavaScript, DHTML
- Backend: Java Servlets, JSP, JDBC

---

# 6. SYSTEM ANALYSIS - UML DIAGRAMS

## 6.1 Use Case Diagram (Description)

### Actors:

- Customer
- Agent
- Admin

### Use Cases:

- Register/Login
- Search Property
- Book Property
- View Bookings
- Make Payments
- Manage Listings
- Generate Reports
- Manage Users
- Approve Listings
- Monitor Transactions

---

### Suggested Figure Placement

- Figure 1: Use Case Diagram of User and Admin interactions

---

## 6.2 Class Diagram (Description)

### Main Classes:

- **User Class**: Base class for system users
- **Customer Class**: Represents property seekers and booking users
- **Agent Class**: Represents listing managers
- **Admin Class**: Represents administrative controllers
- **Property Class**: Stores property details
- **Booking Class**: Handles booking transactions
- **Payment Class**: Handles payment records and receipts

### Relationships:

- One Customer -> Many Bookings
- One Property -> Many Bookings
- One Booking -> One Payment

---

### Suggested Figure Placement

- Figure 2: Class Diagram showing User, Property, and Booking relationships

---

# 7. DATA FLOW DIAGRAM (DFD)

## Level 0 (Context Diagram)

The system interacts with external entities:

- User
- Admin

---

## Level 1 (Main Processes)

- Property Management
- Booking Processing
- Customer Management
- Payment Processing
- Reporting

---

## Level 2 (Booking Flow)

1. User selects property
2. System verifies availability
3. Booking request is processed
4. Booking confirmation is generated

---

### Suggested Figure Placement

- Figure 3: DFD Level 0
- Figure 4: DFD Level 1
- Figure 5: DFD Level 2 for booking flow

---

# 8. ENTITY RELATIONSHIP DIAGRAM (ERD)

## Entities:

- Customer
- Agent
- Admin
- Property
- Booking
- Payment

## Relationships:

- Customer performs booking
- Agent manages property
- Property is linked with booking
- Booking is linked with payment

---

### Suggested Figure Placement

- Figure 6: Entity Relationship Diagram of Users, Properties, and Bookings

---

# 9. DATA MODELING

The database is designed using **3rd Normal Form (3NF)** to eliminate redundancy.

---

## Tables:

### Property Table

- PropId (Primary Key)
- PropName
- Address
- AgentId (Foreign Key)

---

### Customer Table

- CustId (Primary Key)
- CustName
- Email

---

### Bookings Table

- BookingId (Primary Key)
- CustId (Foreign Key)
- PropId (Foreign Key)

---

### Payment Table

- PaymentId (Primary Key)
- BookingId (Foreign Key)
- Amount

---

# 10. MODULES

## 10.1 User Authentication Module

- Secure login and registration
- Session management
- Role-based access control

---

## 10.2 Property Management Module

- Add property
- Edit property
- Delete property
- Image upload
- Duplicate detection

---

## 10.3 Booking Module

- Book property
- View booking history
- Availability checking
- Booking confirmation

---

## 10.4 Payment Module

- Payment handling
- Receipt and invoice support
- Multiple payment method support

---

## 10.5 Search & Validation Module

- Property search and filtering
- Email, phone, date, and numeric validation
- Protection against invalid or unsafe input

---

## 10.6 Admin Module

- Manage users
- Manage properties
- Monitor bookings
- Monitor transactions
- Configure system settings
- Generate reports

---

# 11. PROJECT PLANNING & TIMELINE

Project Duration: 4 Months (March-June)

| Phase | Timeline | Deliverable |
| --- | --- | --- |
| Requirements | Week 1-4 (March) | SRS Document |
| System Design | Weeks 3-8 (Mar-Apr) | Design Document |
| Detailed Design | Weeks 5-12 (Apr-May) | Technical Specs |
| Development | Weeks 7-16 (Apr-Jun) | Source Code |
| Unit Testing | Weeks 11-16 (May-Jun) | Test Reports |
| System Testing | Weeks 13-18 (May-Jun) | Test Reports |
| UAT | Weeks 16-20 (Jun) | UAT Report |
| Deployment | Weeks 17-18 (Jun) | Deployment Report |

### Resource Allocation

- Project Manager (1) - Coordination
- Business Analyst (1) - Requirements
- Architect (1-2) - Design and architecture
- Developers (3-4) - Coding
- Database Admin (1) - Database management
- QA Lead (1) and Testers (2-3) - Testing
- DevOps Engineer (1) - Deployment

---

# 12. SECURITY & VALIDATION

## 12.1 Security Measures

- Password hashing with SHA-256
- Session timeout of 30 minutes
- Role-based access control
- Protection against SQL Injection
- Protection against XSS and CSRF
- SSL/TLS and HTTPS for secure transport
- Backup and restricted database access

---

## 12.2 Validation

- Email format validation using regex
- Phone validation using 10-digit mobile format
- Required field validation
- Numeric input validation for price and amount
- Date validation with booking rules
- Character limits and special character filtering

---

# 13. FUTURE SCOPE

- Payment gateway integration
- Advanced filtering options
- Mobile application development
- AI-based recommendation system
- Virtual property tours
- Live chat and video consultation
- Cloud deployment and analytics expansion

---

# 14. LIMITATIONS

- No integrated payment system
- Internet dependency
- Minor delay risk under high load
- Browser compatibility depends on modern browsers
- Scalability is limited without cloud infrastructure
- Integration dependencies for payment gateway and external services

---

# 15. BIBLIOGRAPHY

### Books:

- Herbert Schildt, Java: The Complete Reference
- Phil Hanna, JSP 2.0: The Complete Reference
- Elmasri and Navathe, Fundamentals of Database Systems
- Ian Somerville, Software Engineering
- Ali Bahrami, Object-Oriented Systems Development
- Martin Fowler, UML Distilled
- Kathy Sierra and Bert Bates, Head First Java

### Websites:

- W3Schools
- MDN Web Docs
- MySQL Documentation
- Oracle Java Documentation
- Apache Tomcat Documentation
- OWASP
- JDBC Tutorial

---

# APPENDIX

(Add Screenshots)

## Live Verification Summary

The application was tested on the local deployment at:

- `http://localhost:8080/real-estate-system/`

The verified workflow covered:

- Agent registration and login
- Property creation
- Admin approval of the property
- Customer registration and login
- Booking submission
- Admin booking confirmation
- Customer payment submission
- Receipt generation
- Reports page and CSV export

### Verified Test Outcome

- Property creation works without requiring an image upload
- Property status moves from `pending` to `approved` to `booked`
- Booking status moves from `pending` to `confirmed` to `completed`
- Payment records are generated successfully with invoice numbers
- Admin reports and exports include the recorded payment data

## Suggested Screenshot Set

- Screenshot 1: Home Page
- Screenshot 2: Registration Page
- Screenshot 3: Login Page
- Screenshot 4: Dashboard
- Screenshot 5: Property Listing Page
- Screenshot 6: Add Property Form
- Screenshot 7: Booking Page
- Screenshot 8: Booking History
- Screenshot 9: Payment Page
- Screenshot 10: Payment Receipt
- Screenshot 11: Admin Panel
- Screenshot 12: Reports Page

## Captured Screenshot Files

The following screenshot assets were generated from the live application and are available in `docs/screenshots/`:

- `01-home.png`
- `02-login.png`
- `03-register.png`
- `04-properties.png`

These can be inserted directly into the report document. The remaining authenticated screens can be captured after signing in with a customer, agent, or admin account.

## Suggested Caption Style

- Figure 7: Home Page of Real Estate Property Listing and Booking System
- Figure 8: Login Interface
- Figure 9: Property Search and Listing Screen
- Figure 10: Booking Submission Screen
- Figure 11: Admin Dashboard
- Figure 12: Reports Interface

## Suggested File System Snapshot

You can also include one screenshot of the project folder structure showing files such as:

- `index.php`
- `login.php`
- `register.php`
- `dashboard.php`
- `properties.php`
- `book.php`
- `admin_dashboard.php`
- `reports.php`
- `php/`
- `css/`
- `js/`
- `uploads/`
