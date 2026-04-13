USE air_trx_db;

ALTER TABLE flights
    ADD COLUMN premium_fare DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER economy_fare,
    ADD COLUMN first_class_fare DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER business_fare;

UPDATE flights SET premium_fare = 5250.00, first_class_fare = 9800.00 WHERE flight_code = 'ATX101';
UPDATE flights SET premium_fare = 6290.00, first_class_fare = 11490.00 WHERE flight_code = 'ATX202';
UPDATE flights SET premium_fare = 4490.00, first_class_fare = 8450.00 WHERE flight_code = 'ATX305';
UPDATE flights SET premium_fare = 5690.00, first_class_fare = 10890.00 WHERE flight_code = 'ATX411';
UPDATE flights SET premium_fare = 5390.00, first_class_fare = 10190.00 WHERE flight_code = 'ATX550';
