<%@ page import="java.util.List" %>
<%@ page import="java.util.Map" %>
<%@ page import="com.realestate.app.model.Property" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Map<String, Integer> stats = (Map<String, Integer>) request.getAttribute("stats");
List<Property> featuredProperties = (List<Property>) request.getAttribute("featuredProperties");
request.setAttribute("pageTitle", "Real Estate System");
%>
<%@ include file="partials/header.jspf" %>

<section class="hero-panel">
    <div class="hero-copy">
        <span class="eyebrow">Property Operations Platform</span>
        <h1>Manage property discovery, booking, payments, and approvals in one streamlined system.</h1>
        <p>Built with Java, JSP/Servlets, MySQL, and lightweight JavaScript for a smoother front-end workflow.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<%= contextPath %>/properties">Browse Properties</a>
            <% if (currentUser == null) { %>
                <a class="btn btn-secondary" href="<%= contextPath %>/register">Create Account</a>
            <% } else { %>
                <a class="btn btn-secondary" href="<%= contextPath %>/dashboard">Open Dashboard</a>
            <% } %>
        </div>
    </div>
    <div class="hero-card">
        <h3>Core Modules</h3>
        <ul class="feature-list">
            <li>Role-based login for customers, agents, and admins</li>
            <li>Searchable property catalogue with approvals</li>
            <li>Booking workflow with status tracking</li>
            <li>Payment recording and invoice receipts</li>
            <li>Admin analytics and operational oversight</li>
        </ul>
    </div>
</section>

<section class="stat-grid">
    <% for (Map.Entry<String, Integer> entry : stats.entrySet()) { %>
        <article class="stat-card">
            <strong><%= entry.getValue() %></strong>
            <span><%= ViewUtil.html(entry.getKey()) %></span>
        </article>
    <% } %>
</section>

<section class="section-head">
    <div>
        <span class="eyebrow">Featured Inventory</span>
        <h2>Recently approved listings</h2>
    </div>
    <a class="btn btn-ghost" href="<%= contextPath %>/properties">View full catalogue</a>
</section>

<section class="card-grid">
    <% if (featuredProperties == null || featuredProperties.isEmpty()) { %>
        <article class="empty-card">
            <h3>No approved properties yet</h3>
            <p>Add a few listings and approve them from the admin panel to populate the home page.</p>
        </article>
    <% } else { %>
        <% for (Property property : featuredProperties) { %>
            <article class="property-card">
                <div class="property-media">
                    <% if (property.getImage() != null && !property.getImage().trim().isEmpty()) { %>
                        <img src="<%= contextPath %>/uploads/<%= property.getImage() %>" alt="<%= ViewUtil.html(property.getTitle()) %>">
                    <% } else { %>
                        <div class="image-fallback">No Image</div>
                    <% } %>
                    <span class="badge <%= ViewUtil.statusClass(property.getStatus()) %>"><%= ViewUtil.html(property.getStatus()) %></span>
                </div>
                <div class="property-body">
                    <h3><%= ViewUtil.html(property.getTitle()) %></h3>
                    <p><%= ViewUtil.html(property.getCity()) %>, <%= ViewUtil.html(property.getLocation()) %></p>
                    <p><strong><%= ViewUtil.currency(property.getPrice()) %></strong> for <%= ViewUtil.html(property.getPurpose()) %></p>
                    <div class="property-meta">
                        <span><%= ViewUtil.html(property.getPropertyType()) %></span>
                        <span><%= property.getAreaSqft() == null ? "Area on request" : property.getAreaSqft() + " sqft" %></span>
                    </div>
                    <div class="property-actions">
                        <a class="btn btn-ghost" href="<%= contextPath %>/properties">View listing</a>
                        <% if (currentUser != null && "customer".equalsIgnoreCase(currentUser.getRole())) { %>
                            <a class="btn btn-primary" href="<%= contextPath %>/bookings/new?propertyId=<%= property.getId() %>">Book visit</a>
                        <% } %>
                    </div>
                </div>
            </article>
        <% } %>
    <% } %>
</section>

<%@ include file="partials/footer.jspf" %>
