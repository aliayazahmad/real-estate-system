<%@ page import="java.util.List" %>
<%@ page import="java.util.Map" %>
<%@ page import="com.realestate.app.model.Booking" %>
<%@ page import="com.realestate.app.model.Payment" %>
<%@ page import="com.realestate.app.model.Property" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Map<String, String> dashboardStats = (Map<String, String>) request.getAttribute("dashboardStats");
List<Property> pendingProperties = (List<Property>) request.getAttribute("pendingProperties");
List<Booking> pendingBookings = (List<Booking>) request.getAttribute("pendingBookings");
List<Payment> recentPayments = (List<Payment>) request.getAttribute("recentPayments");
Map<String, Integer> propertyStatusCounts = (Map<String, Integer>) request.getAttribute("propertyStatusCounts");
Map<String, Integer> bookingStatusCounts = (Map<String, Integer>) request.getAttribute("bookingStatusCounts");
request.setAttribute("pageTitle", "Admin Control Center");
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow">Admin Center</span>
        <h1>Control approvals and monitor platform health</h1>
        <p>The admin workspace centralizes listing review, booking movement, payment activity, and key operational metrics.</p>
    </div>
    <a class="btn btn-primary" href="<%= contextPath %>/admin/reports">Open reports</a>
</section>

<section class="stat-grid">
    <% for (Map.Entry<String, String> entry : dashboardStats.entrySet()) { %>
        <article class="stat-card">
            <strong><%= ViewUtil.html(entry.getValue()) %></strong>
            <span><%= ViewUtil.html(entry.getKey()) %></span>
        </article>
    <% } %>
</section>

<section class="split-grid">
    <article class="panel-card">
        <div class="panel-head">
            <h2>Property status mix</h2>
        </div>
        <div class="mini-chart">
            <% for (Map.Entry<String, Integer> entry : propertyStatusCounts.entrySet()) { %>
                <div class="mini-row">
                    <span><%= ViewUtil.html(entry.getKey()) %></span>
                    <strong><%= entry.getValue() %></strong>
                </div>
            <% } %>
        </div>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <h2>Booking status mix</h2>
        </div>
        <div class="mini-chart">
            <% for (Map.Entry<String, Integer> entry : bookingStatusCounts.entrySet()) { %>
                <div class="mini-row">
                    <span><%= ViewUtil.html(entry.getKey()) %></span>
                    <strong><%= entry.getValue() %></strong>
                </div>
            <% } %>
        </div>
    </article>
</section>

<section class="split-grid">
    <article class="panel-card">
        <div class="panel-head">
            <h2>Pending property approvals</h2>
            <a href="<%= contextPath %>/properties?status=pending">View all</a>
        </div>
        <% if (pendingProperties == null || pendingProperties.isEmpty()) { %>
            <p class="muted-copy">No pending properties right now.</p>
        <% } else { %>
            <div class="stack-list">
                <% for (Property property : pendingProperties) { %>
                    <div class="stack-item stack-item-block">
                        <div>
                            <strong><%= ViewUtil.html(property.getTitle()) %></strong>
                            <p><%= ViewUtil.html(property.getCity()) %>, <%= ViewUtil.html(property.getLocation()) %></p>
                            <small><%= ViewUtil.html(property.getOwnerName()) %> | <%= ViewUtil.currency(property.getPrice()) %></small>
                        </div>
                        <form class="inline-form" method="post" action="<%= contextPath %>/admin/properties/status">
                            <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
                            <input type="hidden" name="id" value="<%= property.getId() %>">
                            <select name="status">
                                <option value="approved">approved</option>
                                <option value="rejected">rejected</option>
                                <option value="pending" selected>pending</option>
                            </select>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                <% } %>
            </div>
        <% } %>
    </article>

    <article class="panel-card">
        <div class="panel-head">
            <h2>Pending bookings</h2>
            <a href="<%= contextPath %>/bookings">View queue</a>
        </div>
        <% if (pendingBookings == null || pendingBookings.isEmpty()) { %>
            <p class="muted-copy">No pending booking approvals.</p>
        <% } else { %>
            <div class="stack-list">
                <% for (Booking booking : pendingBookings) { %>
                    <div class="stack-item stack-item-block">
                        <div>
                            <strong><%= ViewUtil.html(booking.getPropertyTitle()) %></strong>
                            <p><%= ViewUtil.html(booking.getCustomerName()) %> | <%= ViewUtil.html(booking.getCustomerEmail()) %></p>
                            <small>Visit date: <%= ViewUtil.date(booking.getVisitDate()) %></small>
                        </div>
                        <form class="inline-form" method="post" action="<%= contextPath %>/bookings/status">
                            <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
                            <input type="hidden" name="id" value="<%= booking.getId() %>">
                            <select name="status">
                                <option value="pending" selected>pending</option>
                                <option value="confirmed">confirmed</option>
                                <option value="rejected">rejected</option>
                                <option value="cancelled">cancelled</option>
                            </select>
                            <button class="btn btn-primary" type="submit">Update</button>
                        </form>
                    </div>
                <% } %>
            </div>
        <% } %>
    </article>
</section>

<section class="panel-card">
    <div class="panel-head">
        <h2>Recent payments</h2>
        <a href="<%= contextPath %>/payments">Open payments</a>
    </div>
    <% if (recentPayments == null || recentPayments.isEmpty()) { %>
        <p class="muted-copy">No payments recorded yet.</p>
    <% } else { %>
        <div class="stack-list">
            <% for (Payment payment : recentPayments) { %>
                <div class="stack-item">
                    <div>
                        <strong><%= ViewUtil.html(payment.getInvoiceNumber()) %></strong>
                        <p><%= ViewUtil.html(payment.getPropertyTitle()) %></p>
                        <small><%= ViewUtil.html(payment.getCustomerName()) %> | <%= ViewUtil.dateTime(payment.getPaymentDate()) %></small>
                    </div>
                    <div class="align-right">
                        <strong><%= ViewUtil.currency(payment.getAmount()) %></strong>
                        <span class="badge <%= ViewUtil.statusClass(payment.getStatus()) %>"><%= ViewUtil.html(payment.getStatus()) %></span>
                    </div>
                </div>
            <% } %>
        </div>
    <% } %>
</section>

<%@ include file="partials/footer.jspf" %>
