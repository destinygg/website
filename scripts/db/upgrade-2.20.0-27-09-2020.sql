CREATE TABLE IF NOT EXISTS `dfl_payments_purchases` (
    `id` int(14) NOT NULL AUTO_INCREMENT,
    `paymentId` int(14) NOT NULL,
    `subscriptionId` int(14) DEFAULT NULL,
    `donationId` int(14) DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `dfl_payments_purchases_ibfk_1` FOREIGN KEY (`paymentId`) REFERENCES `dfl_orders_payments` (`paymentId`),
    CONSTRAINT `dfl_payments_purchases_ibfk_2` FOREIGN KEY (`subscriptionId`) REFERENCES `dfl_users_subscriptions` (`subscriptionId`),
    CONSTRAINT `dfl_payments_purchases_ibfk_3` FOREIGN KEY (`donationId`) REFERENCES `donations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `dfl_payments_purchases` (`paymentId`, `donationId`, `subscriptionId`)
SELECT `paymentId`, `donationId`, `subscriptionId`
FROM `dfl_orders_payments`;

ALTER TABLE `dfl_orders_payments`
DROP COLUMN IF EXISTS `subscriptionId`,
DROP COLUMN IF EXISTS `donationId`;
