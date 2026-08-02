BEGIN;

DELETE FROM match_events
WHERE match_id IN (SELECT id FROM matches WHERE COALESCE(is_demo, false) = true);

DELETE FROM match_sets
WHERE match_id IN (SELECT id FROM matches WHERE COALESCE(is_demo, false) = true);

DELETE FROM match_reports
WHERE match_id IN (SELECT id FROM matches WHERE COALESCE(is_demo, false) = true);

DELETE FROM match_reschedules
WHERE match_id IN (SELECT id FROM matches WHERE COALESCE(is_demo, false) = true);

DELETE FROM matches
WHERE COALESCE(is_demo, false) = true;

DELETE FROM athlete_statistics
WHERE athlete_id IN (SELECT id FROM athletes WHERE COALESCE(is_demo, false) = true);

DELETE FROM athletes
WHERE COALESCE(is_demo, false) = true;

DELETE FROM standings
WHERE championship_id IN (SELECT id FROM championships WHERE COALESCE(is_demo, false) = true);

DELETE FROM teams
WHERE COALESCE(is_demo, false) = true;

DELETE FROM registrations
WHERE COALESCE(is_demo, false) = true
   OR championship_id IN (SELECT id FROM championships WHERE COALESCE(is_demo, false) = true);

DELETE FROM favorites
WHERE championship_id IN (SELECT id FROM championships WHERE COALESCE(is_demo, false) = true);

DELETE FROM reviews
WHERE championship_id IN (SELECT id FROM championships WHERE COALESCE(is_demo, false) = true);

DELETE FROM championships
WHERE COALESCE(is_demo, false) = true;

COMMIT;
