# Real Estate Hub

This repository contains the approved Real Estate System aligned to the synopsis and the approved sample report.

The board-approved stack is:

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
- `docs/real-estate-project-report.md` - formal project report aligned to the approved sample
- `docs/approved-sample.txt` - extracted text reference from the approved sample PDF
- `pom.xml` - Maven WAR build configuration
- `runtime/` - local runtime assets used during setup and deployment work

## Documentation

- Project report:
  [docs/real-estate-project-report.md](docs/real-estate-project-report.md)
- Approved sample reference:
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

5. Deploy `target/board-approved-real-estate.war` to Tomcat 8.5+.
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

- The Java application source, SQL schema, Maven configuration, and submission report are present in the repository root.
- The formal report draft has been prepared to match the approved sample structure.
- Final database import, Tomcat deployment, and live browser verification still need to be completed on the local machine.
