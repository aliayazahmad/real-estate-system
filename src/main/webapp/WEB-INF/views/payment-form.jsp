<%@ page import="java.util.List" %>
<%@ page import="com.realestate.app.model.Booking" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
Booking booking = (Booking) request.getAttribute("paymentBooking");
List<String> errors = (List<String>) request.getAttribute("errors");
double suggestedAmount = booking == null ? 0 : Math.max(booking.getPropertyPrice() * 0.10, 1000.0);
request.setAttribute("pageTitle", "Record Payment");
%>
<%@ include file="partials/header.jspf" %>

<section class="split-grid">
    <article class="panel-card">
        <span class="eyebrow">Confirmed booking</span>
        <h1><%= ViewUtil.html(booking.getPropertyTitle()) %></h1>
        <p><%= ViewUtil.html(booking.getPropertyCity()) %>, <%= ViewUtil.html(booking.getPropertyLocation()) %></p>
        <p>Visit date: <strong><%= ViewUtil.date(booking.getVisitDate()) %></strong></p>
        <p>Listing price: <strong><%= ViewUtil.currency(booking.getPropertyPrice()) %></strong></p>
        <p class="muted-copy">Use this screen to capture the booking payment and generate a customer receipt.</p>
    </article>

    <article class="form-card">
        <h2>Payment details</h2>
        <% if (errors != null && !errors.isEmpty()) { %>
            <div class="inline-errors">
                <% for (String error : errors) { %>
                    <p><%= ViewUtil.html(error) %></p>
                <% } %>
            </div>
        <% } %>
        <form method="post" action="<%= contextPath %>/payments/new">
            <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
            <input type="hidden" name="bookingId" value="<%= booking.getId() %>">

            <label>Amount</label>
            <input type="number" step="0.01" min="1" name="amount" required value="<%= String.format(java.util.Locale.US, "%.2f", suggestedAmount) %>">

            <label>Payment method</label>
            <select name="paymentMethod">
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Card">Card</option>
                <option value="Cash">Cash</option>
            </select>

            <label>Transaction reference</label>
            <input type="text" name="transactionRef" required placeholder="UTR / bank reference / cash slip no.">

            <label>Notes</label>
            <textarea name="notes" rows="4" placeholder="Optional payment note"></textarea>

            <button class="btn btn-primary btn-block" type="submit">Record payment</button>
        </form>
    </article>
</section>

<%@ include file="partials/footer.jspf" %>
