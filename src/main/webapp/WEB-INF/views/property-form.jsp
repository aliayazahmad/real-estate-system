<%@ page import="java.util.List" %>
<%@ page import="com.realestate.app.model.Property" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Property property = (Property) request.getAttribute("propertyForm");
List<String> errors = (List<String>) request.getAttribute("errors");
List<String> propertyTypes = (List<String>) request.getAttribute("propertyTypes");
List<String> purposeOptions = (List<String>) request.getAttribute("purposeOptions");
List<String> statusOptions = (List<String>) request.getAttribute("statusOptions");
Boolean editing = (Boolean) request.getAttribute("editing");
if (property == null) {
    property = new Property();
}
if (editing == null) {
    editing = Boolean.FALSE;
}
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow"><%= editing ? "Update listing" : "New listing" %></span>
        <h1><%= editing ? "Edit property details" : "Add a new property" %></h1>
        <p>Agents submit listings for approval. Admins can directly control listing workflow status.</p>
    </div>
</section>

<section class="form-card wide-card">
    <% if (errors != null && !errors.isEmpty()) { %>
        <div class="inline-errors">
            <% for (String error : errors) { %>
                <p><%= ViewUtil.html(error) %></p>
            <% } %>
        </div>
    <% } %>
    <form method="post" enctype="multipart/form-data" action="<%= contextPath %><%= editing ? "/properties/edit" : "/properties/new" %>">
        <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
        <% if (editing) { %>
            <input type="hidden" name="id" value="<%= property.getId() %>">
        <% } %>
        <div class="form-grid">
            <div>
                <label>Property title</label>
                <input type="text" name="title" required value="<%= ViewUtil.html(property.getTitle()) %>">
            </div>
            <div>
                <label>City</label>
                <input type="text" name="city" required value="<%= ViewUtil.html(property.getCity()) %>">
            </div>
            <div>
                <label>Location</label>
                <input type="text" name="location" required value="<%= ViewUtil.html(property.getLocation()) %>">
            </div>
            <div>
                <label>Price</label>
                <input type="number" min="0" step="0.01" name="price" required value="<%= property.getPrice() <= 0 ? "" : property.getPrice() %>">
            </div>
            <div>
                <label>Property type</label>
                <select name="propertyType">
                    <% for (String option : propertyTypes) { %>
                        <option value="<%= ViewUtil.html(option) %>" <%= option.equals(property.getPropertyType()) ? "selected" : "" %>><%= ViewUtil.html(option) %></option>
                    <% } %>
                </select>
            </div>
            <div>
                <label>Purpose</label>
                <select name="purpose">
                    <% for (String option : purposeOptions) { %>
                        <option value="<%= ViewUtil.html(option) %>" <%= option.equals(property.getPurpose()) ? "selected" : "" %>><%= ViewUtil.html(option) %></option>
                    <% } %>
                </select>
            </div>
            <div>
                <label>Bedrooms</label>
                <input type="number" min="0" name="bedrooms" value="<%= property.getBedrooms() == null ? "" : property.getBedrooms() %>">
            </div>
            <div>
                <label>Bathrooms</label>
                <input type="number" min="0" name="bathrooms" value="<%= property.getBathrooms() == null ? "" : property.getBathrooms() %>">
            </div>
            <div>
                <label>Area (sqft)</label>
                <input type="number" min="0" name="areaSqft" value="<%= property.getAreaSqft() == null ? "" : property.getAreaSqft() %>">
            </div>
            <div>
                <label>Image</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <% if (property.getImage() != null && !property.getImage().trim().isEmpty()) { %>
                    <small>Current image: <%= ViewUtil.html(property.getImage()) %></small>
                <% } %>
            </div>
            <% if (currentUser != null && "admin".equalsIgnoreCase(currentUser.getRole())) { %>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <% for (String option : statusOptions) { %>
                            <option value="<%= ViewUtil.html(option) %>" <%= option.equals(property.getStatus()) ? "selected" : "" %>><%= ViewUtil.html(option) %></option>
                        <% } %>
                    </select>
                </div>
            <% } %>
        </div>

        <label>Description</label>
        <textarea name="description" rows="6" placeholder="Describe the property, nearby amenities, and selling points."><%= ViewUtil.html(property.getDescription()) %></textarea>

        <% if (currentUser == null || !"admin".equalsIgnoreCase(currentUser.getRole())) { %>
            <p class="form-note">Agent submissions are saved with pending status until an admin reviews them.</p>
        <% } %>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><%= editing ? "Save changes" : "Submit property" %></button>
            <a class="btn btn-secondary" href="<%= contextPath %>/properties">Cancel</a>
        </div>
    </form>
</section>

<%@ include file="partials/footer.jspf" %>
