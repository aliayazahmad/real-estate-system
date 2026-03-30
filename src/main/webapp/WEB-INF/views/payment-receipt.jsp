<%@ page import="com.realestate.app.model.Payment" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Payment payment = (Payment) request.getAttribute("paymentReceipt");
request.setAttribute("pageTitle", "Payment Receipt");
%>
<%@ include file="partials/header.jspf" %>

<section class="receipt-shell">
    <article class="receipt-card">
        <div class="receipt-head">
            <div>
                <span class="eyebrow">Receipt</span>
                <h1><%= ViewUtil.html(payment.getInvoiceNumber()) %></h1>
            </div>
            <span class="badge <%= ViewUtil.statusClass(payment.getStatus()) %>"><%= ViewUtil.html(payment.getStatus()) %></span>
        </div>

        <div class="receipt-grid">
            <div>
                <h3>Customer</h3>
                <p><%= ViewUtil.html(payment.getCustomerName()) %></p>
                <p><%= ViewUtil.html(payment.getCustomerEmail()) %></p>
            </div>
            <div>
                <h3>Property</h3>
                <p><%= ViewUtil.html(payment.getPropertyTitle()) %></p>
                <p><%= ViewUtil.html(payment.getPropertyCity()) %>, <%= ViewUtil.html(payment.getPropertyLocation()) %></p>
            </div>
            <div>
                <h3>Payment</h3>
                <p><strong><%= ViewUtil.currency(payment.getAmount()) %></strong></p>
                <p><%= ViewUtil.html(payment.getPaymentMethod()) %></p>
                <p><%= ViewUtil.html(payment.getTransactionRef()) %></p>
            </div>
            <div>
                <h3>Schedule</h3>
                <p>Visit date: <%= ViewUtil.date(payment.getVisitDate()) %></p>
                <p>Paid on: <%= ViewUtil.dateTime(payment.getPaymentDate()) %></p>
            </div>
        </div>

        <% if (payment.getNotes() != null && !payment.getNotes().trim().isEmpty()) { %>
            <div class="receipt-notes">
                <h3>Notes</h3>
                <p><%= ViewUtil.html(payment.getNotes()) %></p>
            </div>
        <% } %>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<%= contextPath %>/payments">Back to payments</a>
            <button class="btn btn-primary" type="button" onclick="window.print()">Print receipt</button>
        </div>
    </article>
</section>

<%@ include file="partials/footer.jspf" %>
