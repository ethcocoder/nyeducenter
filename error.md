on.ico HTTP/1.1" 404 -
2025-11-01 08:29:38.043 EAT [16278] ERROR:  duplicate key value violates unique constraint "module_order_key"
2025-11-01 08:29:38.043 EAT [16278] DETAIL:  Key ("order")=(3) already exists.
2025-11-01 08:29:38.043 EAT [16278] STATEMENT:  UPDATE module SET "order"=3 WHERE module.id = 6
127.0.0.1 - - [01/Nov/2025 08:29:38] "POST /admin/module/5/delete HTTP/1.1" 500 -
2025-11-01 08:29:38.440 EAT [11056] LOG:  checkpoint starting: time
2025-11-01 08:29:38.788 EAT [11056] LOG:  checkpoint complete: wrote 4 buffers (0.0%); 0 WAL file(s) added, 0 removed, 0 recycled; write=0.317 s, sync=0.006 s, total=0.349 s; sync files=4, longest=0.002 s, average=0.002 s; distance=1 kB, estimate=3 kB; lsn=0/1A4F370, redo lsn=0/1A4F318
 * Detected change in '/data/data/com.termux/files/home/Curriculem/app.py', reloading
 * Restarting with stat
 * Debugger is active!
 * Debugger PIN: 109-483-938
127.0.0.1 - - [01/Nov/2025 08:31:23] "GET /admin HTTP/1.1" 302 -
/data/data/com.termux/files/home/Curriculem/app.py:962: DeprecationWarning: datetime.datetime.utcnow() is deprecated and scheduled for removal in a future version. Use timezone-aware objects to represent datetimes in UTC: datetime.datetime.now(datetime.UTC).
  return {'now': datetime.datetime.utcnow()}
127.0.0.1 - - [01/Nov/2025 08:31:23] "GET /login?next=/admin HTTP/1.1" 200 -
127.0.0.1 - - [01/Nov/2025 08:31:24] "GET /static/css/modern.css HTTP/1.1" 304 -
127.0.0.1 - - [01/Nov/2025 08:31:51] "POST /login?next=/admin HTTP/1.1" 302 -
/data/data/com.termux/files/home/Curriculem/app.py:625: LegacyAPIWarning: The Query.get() method is considered legacy as of the 1.x series of SQLAlchemy and becomes a legacy construct in 2.0. The method is now available as Session.get() (deprecated since: 2.0) (Background on SQLAlchemy 2.0 at: https://sqlalche.me/e/b8d9)
  return User.query.get(int(user_id))
127.0.0.1 - - [01/Nov/2025 08:31:52] "GET /admin HTTP/1.1" 200 -
127.0.0.1 - - [01/Nov/2025 08:31:52] "GET /static/css/modern.css HTTP/1.1" 304 -
127.0.0.1 - - [01/Nov/2025 08:31:52] "GET /static/js/admin_dashboard.js HTTP/1.1" 304 -
2025-11-01 08:31:59.034 EAT [22457] ERROR:  duplicate key value violates unique constraint "module_order_key"
2025-11-01 08:31:59.034 EAT [22457] DETAIL:  Key ("order")=(3) already exists.
2025-11-01 08:31:59.034 EAT [22457] STATEMENT:  UPDATE module SET "order"=3 WHERE module.id = 6
127.0.0.1 - - [01/Nov/2025 08:31:59] "POST /admin/module/5/delete HTTP/1.1" 500 -
