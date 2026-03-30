<%@ page import="java.util.List" %>
<%@ page import="java.util.Map" %>
<%@ page import="com.realestate.app.model.Payment" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Map<String, Integer> propertyStatusCounts = (Map<String, Integer>) request.getAttribute("propertyStatusCounts");
Map<String, Integer> bookingStatusCounts = (Map<String, Integer>) request.getAttribute("bookingStatusCounts");
List<String[]> topCities = (List<String[]>) request.getAttribute("topCities");
List<Payment> recentPayments = (List<Payment>) request.getAttribute("recentPayments");
Double totalRevenue = (Double) request.getAttribute("totalRevenue");
StringBuilder propertySeries = new StringBuilder();
StringBuilder bookingSeries = new StringBuilder();
StringBuilder citySeries = new StringBuilder();
if (propertyStatusCounts != null) {
    for (Map.Entry<String, Integer> entry : propertyStatusCounts.entrySet()) {
        if (propertySeries.length() > 0) {
            propertySeries.append("|");
        }
        propertySeries.append(entry.getKey()).append(":").append(entry.getValue());
    }
}
if (bookingStatusCounts != null) {
    for (Map.Entry<String, Integer> entry : bookingStatusCounts.entrySet()) {
        if (bookingSeries.length() > 0) {
            bookingSeries.append("|");
        }
        bookingSeries.append(entry.getKey()).append(":").append(entry.getValue());
    }
}
if (topCities != null) {
    for (String[] row : topCities) {
        if (citySeries.length() > 0) {
            citySeries.append("|");
        }
        citySeries.append(row[0]).append(":").append(row[1]);
    }
}
request.setAttribute("pageTitle", "Reports & Analytics");
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow">Reports</span>
        <h1>Operational analytics for leadership review</h1>
        <p>These summaries turn day-to-day system activity into a board-friendly overview of listings, bookings, and payment performance.</p>
    </div>
    <div class="hero-actions">
        <a class="btn btn-secondary" href="<%= contextPath %>/admin">Back to admin</a>
    </div>
</section>

<section class="stat-grid">
    <article class="stat-card">
        <strong><%= ViewUtil.currency(totalRevenue == null ? 0 : totalRevenue) %></strong>
        <span>Total revenue captured</span>
    </article>
    <article class="stat-card">
        <strong><%= propertyStatusCounts == null ? 0 : propertyStatusCounts.size() %></strong>
        <span>Property workflow stages</span>
    </article>
    <article class="stat-card">
        <strong><%= bookingStatusCounts == null ? 0 : bookingStatusCounts.size() %></strong>
        <span>Booking workflow stages</span>
    </article>
    <article class="stat-card">
        <strong><%= topCities == null ? 0 : topCities.size() %></strong>
        <span>Tracked market cities</span>
    </article>
</section>

<section class="split-grid">
    <article class="panel-card">
        <div class="panel-head">
            <h2>Property status chart</h2>
        </div>
        <div class="chart-card" data-chart="bars" data-series="<%= ViewUtil.html(propertySeries.toString()) %>"></div>
    </article>
    <article class="panel-card">
        <div class="panel-head">
            <h2>Booking status chart</h2>
        </div>
        <div class="chart-card" data-chart="bars" data-series="<%= ViewUtil.html(bookingSeries.toString()) %>"></div>
    </article>
</section>

<section class="split-grid">
    <article class="panel-card">
        <div class="panel-head">
            <h2>Top cities by listings</h2>
        </div>
        <div class="chart-card" data-chart="bars" data-series="<%= ViewUtil.html(citySeries.toString()) %>"></div>
    </article>
    <article class="panel-card">
        <div class="panel-head">
            <h2>Recent payment log</h2>
        </div>
        <% if (recentPayments == null || recentPayments.isEmpty()) { %>
            <p class="muted-copy">No payments to report yet.</p>
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
    </article>
</section>

<%@ include file="partials/footer.jspf" %>
