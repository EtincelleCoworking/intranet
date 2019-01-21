select
date_format(invoices.date_invoice, "%Y-%m") as period,
SUM(invoices_items.amount) as total,
if(`locations`.`name` is null,cities.name,concat(cities.name, ' > ',  `locations`.`name`)) as `kind`

from `invoices_items`
inner join `invoices` on `invoice_id` = `invoices`.`id` and `type` = 'F'
left outer join `organisations` on `organisation_id` = `organisations`.`id`
left outer join `ressources` on `ressource_id` = `ressources`.`id`
left outer join `locations` on `location_id` = `locations`.`id`
left outer join cities on city_id = cities.id

where (`organisations`.`is_founder` = '0' or `organisation_id` is null)
AND ressources.ressource_kind_id NOT IN (1, 4)

group by `period`, kind
order by `period` desc, kind ASC


select
date_format(invoices.date_invoice, "%Y-%m") as period,
SUM(invoices_items.amount) as total,
if(`locations`.`name` is null,cities.name,concat(cities.name, ' > ',  `locations`.`name`)) as `kind`

from `invoices_items`
inner join `invoices` on `invoice_id` = `invoices`.`id` and `type` = 'F'
left outer join `organisations` on `organisation_id` = `organisations`.`id`
join `ressources` on invoices_items.ressource_id = `ressources`.`id`

join users u on u.id = invoices_items.subscription_user_id
join `locations` on u.default_location_id = `locations`.`id`
left outer join cities on city_id = cities.id

where (`organisations`.`is_founder` = '0' or `organisation_id` is null)
AND ressources.ressource_kind_id = 1

group by `period`, kind
order by `period` desc, kind ASC

curl -X POST -H 'Content-type: application/json' --data '{"text":"Laetia Chesse est la !","attachments":[{"title":"Laetitia Chesse","text":"Marketing - SEO, SEA, SEM\n\nVoir son profil sur <http:\/\/intranet.coworking-toulouse.com\/profile\/", "image_url":"http:\/\/www.gravatar.com\/avatar\/8db9fc9bc5bbabd6af15e7f7d2f45d99?d=mm&s=80"},{"pretext":"Citation du jour :","author_name":"Frederick (II) the Great","text":"I begin by taking. I shall find scholars later to demonstrate my perfect right."}]}' https://hooks.slack.com/services/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
