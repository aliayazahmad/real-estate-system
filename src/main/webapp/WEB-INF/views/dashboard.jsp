<%@ page import="java.util.List" %>
<%@ page import="java.util.Map" %>
<%@ page import="com.realestate.app.model.Booking" %>
<%@ page import="com.realestate.app.model.Property" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Map<String, Integer> stats = (Map<String, Integer>) request.getAttribute("stats");
List<Booking> recentBookings = (List<Booking>) request.getAttribute("recentBookings");
List<Property> recentProperties = (List<Property>) request.getAttribute("recentProperties");
Boolean agentMode = (Boolean) request.getAttribute("agentMode");
if (agentMode == null) {
    agentMode = Boolean.FALSE;
}
request.setAttribute("pageTitle", "Dashboard");
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow">Dashboard</span>
        <h1>Hello, <%= ViewUtil.html(currentUser.getName()) %></h1>
        <p>Track your key activity at a glance.</p>
    </div>
    <div class="hero-actions">
        <% if (agentMode) { %>
            <a class="btn btn-primary" href="<%= contextPath %>/properties/new">Add property</a>
        <% } else { %>
            <a class="btn btn-primary" href="<%= contextPath %>/properties">Browse listings</a>
        <% } %>
        <a class="btn btn-secondary" href="<%= contextPath %>/profile">Edit profile</a>
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

<section class="split-grid">
    <article class="panel-card">
        <div class="panel-head">
            <h2><%= agentMode ? "Recent Listings" : "Available Listings" %></h2>
            <a href="<%= contextPath %>/properties">Open properties</a>
        </div>
        <% if (recentProperties == null || recentProperties.isEmpty()) { %>
            <p class="muted-copy">No properties to show yet.</p>
        <% } else { %>
            <div class="stack-list">
                <% for (Property property : recentProperties) { %>
                    <div class="stack-item">
                        <div>
                            <strong><%= ViewUtil.html(property.getTitle()) %></strong>
                            <p><%= ViewUtil.html(property.getCity()) %>, <%= ViewUtil.html(property.getLocation()) %></p>
                        </div>
                        <div class="align-right">
                            <strong><%= ViewUtil.currency(property.getPrice()) %></strong>
                            <span class="badge <%= ViewUtil.statusClass(property.getStatus()) %>"><%= ViewUtil.html(property.getStatus()) %></span>
                        </div>
                    </div>
                <% } %>
            </div>
        <% } %>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <h2><%= agentMode ? "Booking Requests" : "My Bookings" %></h2>
            <a href="<%= contextPath %>/bookings">Open bookings</a>
        </div>
        <% if (recentBookings == null || recentBookings.isEmpty()) { %>
            <p class="muted-copy">No booking activity yet.</p>
        <% } else { %>
            <div class="stack-list">
                <% for (Booking booking : recentBookings) { %>
                    <div class="stack-item">
                        <div>
                            <strong><%= ViewUtil.html(booking.getPropertyTitle()) %></strong>
                            <p><%= ViewUtil.date(booking.getVisitDate()) %></p>
                            <% if (agentMode) { %>
                                <small><%= ViewUtil.html(booking.getCustomerName()) %> | <%= ViewUtil.html(booking.getCustomerEmail()) %></small>
                            <% } %>
                        </div>
                        <div class="align-right">
                            <span class="badge <%= ViewUtil.statusClass(booking.getStatus()) %>"><%= ViewUtil.html(booking.getStatus()) %></span>
                            <% if (!agentMode && booking.getPaymentStatus() != null) { %>
                                <small>Payment: <%= ViewUtil.html(booking.getPaymentStatus()) %></small>
                            <% } %>
                        </div>
                    </div>
                <% } %>
            </div>
        <% } %>
    </article>
</section>

<%@ include file="partials/footer.jspf" %>
