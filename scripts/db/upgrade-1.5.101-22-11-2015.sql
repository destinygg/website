# Add payment profile columns to subscription table
ALTER TABLE `dfl_users_subscriptions`
ADD COLUMN `billingStartDate` DATETIME NULL AFTER `paymentProfileId`,
ADD COLUMN `billingNextDate` DATETIME NULL AFTER `billingStartDate`,
ADD COLUMN `paymentStatus` VARCHAR(50) NULL AFTER `billingNextDate`;

# Add subscription to payment table
ALTER TABLE `dfl_orders_payments`
ADD COLUMN `subscriptionId` INT(14) NULL AFTER `paymentId`;

# Copy the subscriptionId from subscriptions table using orderId
UPDATE dfl_orders_payments p
  INNER JOIN dfl_users_subscriptions s ON (s.orderId = p.orderId)
SET p.subscriptionId = s.subscriptionId
WHERE p.subscriptionId IS NULL;

# Rename the current INT paymentProfileId field
# Add the new paymentProfileId VARCHAR field
ALTER TABLE `dfl_users_subscriptions`
CHANGE `paymentProfileId` `_paymentProfileId` VARCHAR(255) CHARSET utf8 COLLATE utf8_unicode_ci NULL;
ALTER TABLE `dfl_users_subscriptions`
ADD COLUMN `paymentProfileId` VARCHAR(255) NULL AFTER `_paymentProfileId`;

# Copy the payment profile info into the subscriptions table
UPDATE `dfl_users_subscriptions` s
  INNER JOIN `dfl_orders_payment_profiles` pp ON (pp.profileId = s._paymentProfileId)
SET s.billingStartDate = pp.billingStartDate,
  s.billingNextDate = pp.billingNextDate,
  s.paymentStatus = pp.state,
  s.paymentProfileId = pp.paymentProfileId;

# Stopped using paypal payment status, started using our own internal.
UPDATE dfl_users_subscriptions SET paymentStatus = 'Active' WHERE paymentStatus = 'ActiveProfile';
UPDATE dfl_users_subscriptions SET paymentStatus = 'Cancelled' WHERE paymentStatus = 'CancelledProfile';

# Old subscriptions where made NON recurring when expired - we no longer need to do this. - this step is just legacy clean up
UPDATE `dfl_users_subscriptions`
SET recurring = 1 WHERE paymentProfileId IS NOT NULL;

# Drop the orderId from payments
ALTER TABLE dfl_orders_payments DROP COLUMN orderId;

# Drop the _paymentProfileId and orderId from subscriptions
ALTER TABLE dfl_users_subscriptions
DROP COLUMN _paymentProfileId,
DROP COLUMN orderId;

# Finally, drop the non-required tables
DROP TABLE `dfl_orders`;
DROP TABLE `dfl_orders_payment_profiles`;
DROP TABLE `dfl_orders_items`;