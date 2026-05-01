-- Optimized fetching query
SELECT 
    leads.id, 
    leads.firstname, 
    leads.lastname, 
    leads.gender, 
    leads.company_name, 
    leads.business, 
    leads.streetname, 
    leads.housenumber, 
    leads.suffix, 
    leads.postcode, 
    leads.city, 
    leads.status, 
    leads.organisation_id, 
    leads.team_id, 
    leads.planned_user_id, 
    leads.created_by,
    DATE_FORMAT(leads.created_at, "%d-%m-%Y %H:%i") AS created_datetime,
    DATE_FORMAT(leads.updated_at, "%d-%m-%Y %H:%i") AS updated_datetime,
    DATE_FORMAT(leads.planned_date, "%d-%m-%Y") AS planned_date_formatted,
    DATE_FORMAT(leads.planned_from, "%H:%i") AS planned_from_time,
    DATE_FORMAT(leads.planned_to, "%H:%i") AS planned_to_time
FROM leads
WHERE leads.account_id = 1 
  AND leads.deleted_at IS NULL
ORDER BY leads.id DESC
LIMIT 100 OFFSET 0;