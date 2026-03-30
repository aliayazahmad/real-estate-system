<%@ page import="java.util.List" %>
<%@ page import="com.realestate.app.model.Payment" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
List<Payment> payments = (List<Payment>) request.getAttribute("payments");
Boolean adminMode = (Boolean) request.getAttribute("adminMode");
if (adminMode == null) {
    adminMode = Boolean.FALSE;
}
request.setAttribute("pageTitle", "Payments");
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow">Payments</span>
        <h1><%= adminMode ? "Recent platform payments" : "Your payment receipts" %></h1>
        <p><%= adminMode ? "Monitor monetary activity connected to property bookings." : "Every successful payment generates a receipt with booking and property details." %></p>
    </div>
</section>

<section class="stack-panel">
    <% if (payments == null || payments.isEmpty()) { %>
        <article class="empty-card">
            <h3>No payments available</h3>
            <p>Once a customer pays for a confirmed booking, the transaction will appear here.</p>
        </article>
    <% } else { %>
        <% for (Payment payment : payments) { %>
            <article class="booking-card">
                <div class="booking-main">
                    <div>
                        <h3><%= ViewUtil.html(payment.getPropertyTitle()) %></h3>
                        <p><%= ViewUtil.html(payment.getPropertyCity()) %>, <%= ViewUtil.html(payment.getPropertyLocation()) %></p>
                        <p>Invoice: <strong><%= ViewUtil.html(payment.getInvoiceNumber()) %></strong></p>
                        <p>Paid on <%= ViewUtil.dateTime(payment.getPaymentDate()) %> by <%= ViewUtil.html(payment.getPaymentMethod()) %></p>
                        <% if (adminMode) { %>
                            <p class="owner-line">Customer: <%= ViewUtil.html(payment.getCustomerName()) %> | <%= ViewUtil.html(payment.getCustomerEmail()) %></p>
                        <% } %>
                    </div>
                    <div class="align-right">
                        <strong><%= ViewUtil.currency(payment.getAmount()) %></strong>
                        <span class="badge <%= ViewUtil.statusClass(payment.getStatus()) %>"><%= ViewUtil.html(payment.getStatus()) %></span>
                    </div>
                </div>
                <div class="booking-actions">
                    <a class="btn btn-secondary" href="<%= contextPath %>/payments/receipt?id=<%= payment.getId() %>">Open receipt</a>
                </div>
            </article>
        <% } %>
    <% } %>
</section>

<%@ include file="partials/footer.jspf" %>
