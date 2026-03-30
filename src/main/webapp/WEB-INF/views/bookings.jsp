<%@ page import="java.util.List" %>
<%@ page import="com.realestate.app.model.Booking" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
List<Booking> bookings = (List<Booking>) request.getAttribute("bookings");
List<String> statusChoices = (List<String>) request.getAttribute("statusChoices");
Boolean agentMode = (Boolean) request.getAttribute("agentMode");
Boolean adminMode = (Boolean) request.getAttribute("adminMode");
if (agentMode == null) {
    agentMode = Boolean.FALSE;
}
if (adminMode == null) {
    adminMode = Boolean.FALSE;
}
request.setAttribute("pageTitle", "Bookings");
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow">Bookings</span>
        <h1><%= adminMode ? "Booking approvals" : agentMode ? "Booking requests for your listings" : "Your booking history" %></h1>
        <p><%= adminMode ? "Review the latest incoming requests and coordinate the approval pipeline." : agentMode ? "Confirm, reject, or complete requests from interested customers." : "Track visit requests, confirmations, and payment readiness." %></p>
    </div>
    <% if (!agentMode && !adminMode) { %>
        <a class="btn btn-primary" href="<%= contextPath %>/properties">Find a property</a>
    <% } %>
</section>

<section class="stack-panel">
    <% if (bookings == null || bookings.isEmpty()) { %>
        <article class="empty-card">
            <h3>No bookings yet</h3>
            <p>The booking module is ready. Once requests are created, they will show up here with workflow actions.</p>
        </article>
    <% } else { %>
        <% for (Booking booking : bookings) { %>
            <article class="booking-card">
                <div class="booking-main">
                    <div>
                        <h3><%= ViewUtil.html(booking.getPropertyTitle()) %></h3>
                        <p><%= ViewUtil.html(booking.getPropertyCity()) %>, <%= ViewUtil.html(booking.getPropertyLocation()) %></p>
                        <p>Requested visit: <strong><%= ViewUtil.date(booking.getVisitDate()) %></strong></p>
                        <% if (booking.getMessage() != null && !booking.getMessage().trim().isEmpty()) { %>
                            <p class="muted-copy"><%= ViewUtil.html(booking.getMessage()) %></p>
                        <% } %>
                        <% if (agentMode || adminMode) { %>
                            <p class="owner-line">Customer: <%= ViewUtil.html(booking.getCustomerName()) %> | <%= ViewUtil.html(booking.getCustomerEmail()) %></p>
                        <% } %>
                    </div>
                    <div class="align-right">
                        <span class="badge <%= ViewUtil.statusClass(booking.getStatus()) %>"><%= ViewUtil.html(booking.getStatus()) %></span>
                        <% if (!agentMode && !adminMode && booking.getPaymentStatus() != null) { %>
                            <small>Payment: <%= ViewUtil.html(booking.getPaymentStatus()) %></small>
                        <% } %>
                    </div>
                </div>
                <div class="booking-actions">
                    <% if (!agentMode && !adminMode) { %>
                        <% if ("confirmed".equalsIgnoreCase(booking.getStatus()) && booking.getPaymentId() == null) { %>
                            <a class="btn btn-primary" href="<%= contextPath %>/payments/new?bookingId=<%= booking.getId() %>">Make payment</a>
                        <% } %>
                        <% if (booking.getPaymentId() != null) { %>
                            <a class="btn btn-secondary" href="<%= contextPath %>/payments/receipt?id=<%= booking.getPaymentId() %>">View receipt</a>
                        <% } %>
                        <% if ("pending".equalsIgnoreCase(booking.getStatus()) || "confirmed".equalsIgnoreCase(booking.getStatus())) { %>
                            <form method="post" action="<%= contextPath %>/bookings/cancel">
                                <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
                                <input type="hidden" name="id" value="<%= booking.getId() %>">
                                <button class="btn btn-danger" type="submit">Cancel booking</button>
                            </form>
                        <% } %>
                    <% } else { %>
                        <form class="inline-form" method="post" action="<%= contextPath %>/bookings/status">
                            <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
                            <input type="hidden" name="id" value="<%= booking.getId() %>">
                            <select name="status">
                                <% for (String option : statusChoices) { %>
                                    <option value="<%= ViewUtil.html(option) %>" <%= option.equalsIgnoreCase(booking.getStatus()) ? "selected" : "" %>><%= ViewUtil.html(option) %></option>
                                <% } %>
                            </select>
                            <button class="btn btn-primary" type="submit">Update</button>
                        </form>
                    <% } %>
                </div>
            </article>
        <% } %>
    <% } %>
</section>

<%@ include file="partials/footer.jspf" %>
