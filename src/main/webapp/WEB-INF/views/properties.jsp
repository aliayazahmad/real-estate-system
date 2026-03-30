<%@ page import="java.util.List" %>
<%@ page import="java.util.Map" %>
<%@ page import="com.realestate.app.model.Property" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Map<String, String> filters = (Map<String, String>) request.getAttribute("filters");
List<Property> properties = (List<Property>) request.getAttribute("properties");
List<String[]> cityOptions = (List<String[]>) request.getAttribute("cityOptions");
List<String> propertyTypes = (List<String>) request.getAttribute("propertyTypes");
List<String> purposeOptions = (List<String>) request.getAttribute("purposeOptions");
List<String> statusOptions = (List<String>) request.getAttribute("statusOptions");
if (filters == null) {
    filters = new java.util.LinkedHashMap<String, String>();
}
request.setAttribute("pageTitle", "Properties");
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow">Property Catalogue</span>
        <h1>Search, filter, and manage real-estate inventory</h1>
        <p>Customers see approved listings. Agents and admins also see workflow status for their managed properties.</p>
    </div>
    <% if (currentUser != null && ("agent".equalsIgnoreCase(currentUser.getRole()) || "admin".equalsIgnoreCase(currentUser.getRole()))) { %>
        <a class="btn btn-primary" href="<%= contextPath %>/properties/new">Add property</a>
    <% } %>
</section>

<section class="panel-card">
    <form class="filter-grid" method="get" action="<%= contextPath %>/properties">
        <div>
            <label>Search</label>
            <input type="text" name="q" value="<%= ViewUtil.html(filters.get("q")) %>" placeholder="Title, city, location">
        </div>
        <div>
            <label>City</label>
            <select name="city">
                <option value="">All cities</option>
                <% if (cityOptions != null) { %>
                    <% for (String[] row : cityOptions) { %>
                        <option value="<%= ViewUtil.html(row[0]) %>" <%= row[0].equals(filters.get("city")) ? "selected" : "" %>><%= ViewUtil.html(row[0]) %></option>
                    <% } %>
                <% } %>
            </select>
        </div>
        <div>
            <label>Property type</label>
            <select name="property_type">
                <option value="">All types</option>
                <% for (String option : propertyTypes) { %>
                    <option value="<%= ViewUtil.html(option) %>" <%= option.equals(filters.get("property_type")) ? "selected" : "" %>><%= ViewUtil.html(option) %></option>
                <% } %>
            </select>
        </div>
        <div>
            <label>Purpose</label>
            <select name="purpose">
                <option value="">Any purpose</option>
                <% for (String option : purposeOptions) { %>
                    <option value="<%= ViewUtil.html(option) %>" <%= option.equals(filters.get("purpose")) ? "selected" : "" %>><%= ViewUtil.html(option) %></option>
                <% } %>
            </select>
        </div>
        <div>
            <label>Min price</label>
            <input type="number" step="0.01" min="0" name="min_price" value="<%= ViewUtil.html(filters.get("min_price")) %>">
        </div>
        <div>
            <label>Max price</label>
            <input type="number" step="0.01" min="0" name="max_price" value="<%= ViewUtil.html(filters.get("max_price")) %>">
        </div>
        <% if (currentUser != null && ("agent".equalsIgnoreCase(currentUser.getRole()) || "admin".equalsIgnoreCase(currentUser.getRole()))) { %>
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">All statuses</option>
                    <% for (String option : statusOptions) { %>
                        <option value="<%= ViewUtil.html(option) %>" <%= option.equals(filters.get("status")) ? "selected" : "" %>><%= ViewUtil.html(option) %></option>
                    <% } %>
                </select>
            </div>
        <% } %>
        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Apply filters</button>
            <a class="btn btn-secondary" href="<%= contextPath %>/properties">Reset</a>
        </div>
    </form>
</section>

<section class="card-grid">
    <% if (properties == null || properties.isEmpty()) { %>
        <article class="empty-card">
            <h3>No properties matched your filters</h3>
            <p>Try broadening the search or adding a new listing if you are signed in as an agent or admin.</p>
        </article>
    <% } else { %>
        <% for (Property property : properties) { %>
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
                    <div class="property-topline">
                        <h3><%= ViewUtil.html(property.getTitle()) %></h3>
                        <strong><%= ViewUtil.currency(property.getPrice()) %></strong>
                    </div>
                    <p><%= ViewUtil.html(property.getCity()) %>, <%= ViewUtil.html(property.getLocation()) %></p>
                    <p><%= ViewUtil.html(property.getDescription()) %></p>
                    <div class="property-meta">
                        <span><%= ViewUtil.html(property.getPropertyType()) %></span>
                        <span><%= ViewUtil.html(property.getPurpose()) %></span>
                        <span><%= property.getBedrooms() == null ? "-" : property.getBedrooms() %> bed</span>
                        <span><%= property.getBathrooms() == null ? "-" : property.getBathrooms() %> bath</span>
                        <span><%= property.getAreaSqft() == null ? "-" : property.getAreaSqft() %> sqft</span>
                    </div>
                    <p class="owner-line">Owner: <%= ViewUtil.html(property.getOwnerName()) %> <% if (property.getOwnerEmail() != null && !property.getOwnerEmail().isEmpty()) { %>| <%= ViewUtil.html(property.getOwnerEmail()) %><% } %></p>
                    <div class="property-actions">
                        <% if (currentUser == null) { %>
                            <a class="btn btn-secondary" href="<%= contextPath %>/login">Login to book</a>
                        <% } else if ("customer".equalsIgnoreCase(currentUser.getRole()) && "approved".equalsIgnoreCase(property.getStatus())) { %>
                            <a class="btn btn-primary" href="<%= contextPath %>/bookings/new?propertyId=<%= property.getId() %>">Book visit</a>
                        <% } %>

                        <% if (currentUser != null && ("admin".equalsIgnoreCase(currentUser.getRole()) || property.getUserId() == currentUser.getId())) { %>
                            <a class="btn btn-ghost" href="<%= contextPath %>/properties/edit?id=<%= property.getId() %>">Edit</a>
                            <form method="post" action="<%= contextPath %>/properties/delete" onsubmit="return confirm('Delete this property?');">
                                <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
                                <input type="hidden" name="id" value="<%= property.getId() %>">
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        <% } %>
                    </div>
                </div>
            </article>
        <% } %>
    <% } %>
</section>

<%@ include file="partials/footer.jspf" %>
