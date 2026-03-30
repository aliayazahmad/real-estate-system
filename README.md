# Real Estate Hub

This repository contains the Real Estate System application, deployment assets, and supporting documentation.

The primary stack is:

- Java
- JSP / Servlets
- MySQL
- HTML / CSS / JavaScript

Legacy PHP files remain in the repository from earlier work, but the Java/JSP/MySQL implementation is the approved source of truth.

## Included Modules

- Customer and agent registration/login
- Role-based dashboard and profile management
- Property add/edit/delete with image upload
- Property approval workflow for admin
- Public property search and filtering
- Customer booking requests with visit dates
- Agent/admin booking status management
- Payment capture with invoice receipt
- Admin reporting dashboard with visual summaries

## Project Structure

- `src/main/java` - models, DAOs, utilities, and servlet controllers
- `src/main/webapp/WEB-INF/views` - JSP pages and shared partials
- `src/main/webapp/assets` - CSS and JavaScript
- `sql/schema.sql` - database schema
- `docs/real-estate-project-report.md` - project documentation
- `docs/approved-sample.txt` - reference notes extracted from the sample PDF
- `pom.xml` - Maven WAR build configuration
- `runtime/` - local runtime assets used during setup and deployment work

## Documentation

- Project report:
  [docs/real-estate-project-report.md](docs/real-estate-project-report.md)
- Reference notes:
  [docs/approved-sample.txt](docs/approved-sample.txt)

## Fast Start

1. Install a JDK and Maven.
2. Create the database by running `sql/schema.sql`.
3. Configure database environment variables when needed:
   - `REAL_ESTATE_DB_HOST`
   - `REAL_ESTATE_DB_PORT`
   - `REAL_ESTATE_DB_NAME`
   - `REAL_ESTATE_DB_USER`
   - `REAL_ESTATE_DB_PASS`
4. Build the WAR:

```bash
mvn clean package
```

5. Deploy `target/real-estate-system.war` to Tomcat 8.5+ after building.
6. Register an account and promote one user to `admin`:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

## Main Routes

- `/` - home page
- `/login`, `/register`, `/logout`
- `/dashboard`, `/profile`
- `/properties`, `/properties/new`, `/properties/edit`
- `/bookings`, `/bookings/new`
- `/payments`, `/payments/new`, `/payments/receipt`
- `/admin`, `/admin/reports`

## Current Status

- The Java application source, SQL schema, Maven configuration, and documentation are present in the repository root.
- The documentation set includes the current project write-up and reference notes.
- Final database import, Tomcat deployment, and live browser verification still need to be completed on the local machine.
