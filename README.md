# petwatch-app

PetWatch is a full-stack web application I built to help users report, track, and manage missing pets and pet sightings within their community. Users can browse missing pets, report sightings, and view everything on an interactive map.

One of the main things I focused on was performance. To make the map work smoothly even with thousands of pet sighting entries, I implemented marker clustering using Leaflet. Instead of rendering every marker individually, nearby sightings are grouped together dynamically, which keeps the map fast, responsive, and easy to navigate.

Features
Report and track missing pets
Interactive map for pet sightings
Marker clustering for scalable map performance
Search and filtering functionality
User authentication and session management
Responsive UI design
Tech Stack
ASP.NET / C#
SQL Server
HTML, CSS, JavaScript, Bootstrap
Leaflet.js + Marker Clustering
