# ITCS333 Course Page

## Overview
This is a static HTML/CSS/JavaScript course website for the ITCS333 Web Development course. The project is a classroom assignment that includes various pages for course management, student resources, assignments, discussions, and more.

## Team Members
- 202309994 - MOHSIN ALI SAAD ALABBAS
- 202309507 - HUSAIN ALI AHMED ALAHMED
- 202307977 - AHMED ALI HASAN MARDHI MOHAMED ALI
- 202308207 - HADI ALI ABDULHASAN ALSAFI
- 202310536 - ALAA WAHEED ISMAEEL AHMED

## Task Assignments
- AHMED - TASK 1
- HADI - TASK 2
- HUSAIN - TASK 3
- MOHSIN - TASK 4
- ALAA - TASK 5

## Project Structure
```
/
├── index.html              # Main homepage with navigation
├── server.js               # Node.js HTTP server for serving static files
├── src/
│   ├── admin/              # Admin pages for managing students
│   ├── assignments/        # Assignment pages (admin & student views)
│   ├── auth/               # Login page and authentication
│   ├── common/             # Shared styles
│   ├── discussion/         # Discussion board
│   ├── resources/          # Course resources
│   └── weekly/             # Weekly breakdown pages
└── assets/                 # Static assets
```

## Technology Stack
- **Frontend**: Pure HTML, CSS, JavaScript (no frameworks)
- **Data**: JSON files for mock data
- **Server**: Node.js HTTP server (for static file serving)

## Current State
The project has been imported from GitHub and set up to run on Replit. A simple Node.js HTTP server has been configured to serve the static files on port 5000.

## Recent Changes
- **2025-10-30**: Initial Replit setup
  - Created Node.js HTTP server (server.js) with cache-control headers
  - Configured workflow to serve on 0.0.0.0:5000
  - Set up deployment configuration

## Running the Project
The project runs automatically via the configured workflow. The server serves static HTML/CSS/JavaScript files from the root directory.

- **Development URL**: Available in the Webview tab
- **Port**: 5000 (0.0.0.0)

## Features
- Login page with client-side validation
- Admin portal for managing students
- Course assignments with detail pages
- Weekly breakdown management
- Course resources
- Discussion board
- All pages include comment/discussion functionality

## Notes
- This is a static site with no backend server or database
- JSON files in `api/` directories serve as mock data
- The site uses TODO comments as part of the classroom assignment structure
