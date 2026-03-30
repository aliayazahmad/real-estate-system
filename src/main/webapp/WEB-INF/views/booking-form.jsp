<%@ page import="java.time.LocalDate" %>
<%@ page import="java.util.List" %>
<%@ page import="com.realestate.app.model.Property" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Property property = (Property) request.getAttribute("bookingProperty");
List<String> errors = (List<String>) request.getAttribute("errors");
request.setAttribute("pageTitle", "Book Property Visit");
%>
<%@ include file="partials/header.jspf" %>

<section class="split-grid">
    <article class="panel-card">
        <span class="eyebrow">Selected property</span>
        <h1><%= ViewUtil.html(property.getTitle()) %></h1>
        <p><%= ViewUtil.html(property.getCity()) %>, <%= ViewUtil.html(property.getLocation()) %></p>
        <p><strong><%= ViewUtil.currency(property.getPrice()) %></strong> for <%= ViewUtil.html(property.getPurpose()) %></p>
        <p><%= ViewUtil.html(property.getDescription()) %></p>
        <div class="property-meta">
            <span><%= ViewUtil.html(property.getPropertyType()) %></span>
            <span><%= property.getBedrooms() == null ? "-" : property.getBedrooms() %> bed</span>
            <span><%= property.getBathrooms() == null ? "-" : property.getBathrooms() %> bath</span>
            <span><%= property.getAreaSqft() == null ? "-" : property.getAreaSqft() %> sqft</span>
        </div>
    </article>

    <article class="form-card">
        <h2>Schedule a visit</h2>
        <% if (errors != null && !errors.isEmpty()) { %>
            <div class="inline-errors">
                <% for (String error : errors) { %>
                    <p><%= ViewUtil.html(error) %></p>
                <% } %>
            </div>
        <% } %>
        <form method="post" action="<%= contextPath %>/bookings/new">
            <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
            <input type="hidden" name="propertyId" value="<%= property.getId() %>">
            <label>Preferred visit date</label>
            <input type="date" name="visitDate" required min="<%= LocalDate.now() %>">

            <label>Message for the agent</label>
            <textarea name="message" rows="5" placeholder="Add any time preference or booking note."></textarea>

            <button class="btn btn-primary btn-block" type="submit">Submit booking request</button>
        </form>
    </article>
</section>

<%@ include file="partials/footer.jspf" %>
