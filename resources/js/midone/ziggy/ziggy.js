const Ziggy = {"url":"http:\/\/localhost:8000","port":8000,"defaults":{},"routes":{"api.auth":{"uri":"api\/auth","methods":["POST"]},"api.get.db.company.company.read":{"uri":"api\/get\/dashboard\/company\/company\/read","methods":["GET","HEAD"]},"api.get.db.company.company.default":{"uri":"api\/get\/dashboard\/company\/company\/default","methods":["GET","HEAD"]},"api.get.db.company.company.read.all_active":{"uri":"api\/get\/dashboard\/company\/company\/read\/all\/active","methods":["GET","HEAD"]},"api.get.db.company.branch.read":{"uri":"api\/get\/dashboard\/company\/branch\/read","methods":["GET","HEAD"]},"api.get.db.company.warehouse.read":{"uri":"api\/get\/dashboard\/company\/warehouse\/read","methods":["GET","HEAD"]},"api.get.db.supplier.supplier.read":{"uri":"api\/get\/dashboard\/supplier\/supplier\/read","methods":["GET","HEAD"]},"api.get.db.supplier.common.list.payment_term":{"uri":"api\/get\/dashboard\/supplier\/common\/list\/payment_term","methods":["GET","HEAD"]},"api.get.db.product.brand.read":{"uri":"api\/get\/dashboard\/product\/brand\/read","methods":["GET","HEAD"]},"api.get.db.product.product_group.read":{"uri":"api\/get\/dashboard\/product\/product_group\/read","methods":["GET","HEAD"]},"api.get.db.product.product.read":{"uri":"api\/get\/dashboard\/product\/product\/read","methods":["GET","HEAD"]},"api.get.db.product.service.read":{"uri":"api\/get\/dashboard\/product\/service\/read","methods":["GET","HEAD"]},"api.get.db.product.unit.read":{"uri":"api\/get\/dashboard\/product\/unit\/read","methods":["GET","HEAD"]},"api.get.db.product.common.list.product_type":{"uri":"api\/get\/dashboard\/product\/common\/list\/product_type","methods":["GET","HEAD"]},"api.get.db.admin.users.read":{"uri":"api\/get\/dashboard\/admin\/users\/read","methods":["GET","HEAD"]},"api.get.db.admin.users.roles.read":{"uri":"api\/get\/dashboard\/admin\/users\/roles\/read","methods":["GET","HEAD"]},"api.get.db.devtool.test":{"uri":"api\/get\/dashboard\/devtool\/test","methods":["GET","HEAD"]},"api.get.db.core.profile.read":{"uri":"api\/get\/dashboard\/core\/profile\/read","methods":["GET","HEAD"]},"api.get.db.core.inbox.list.thread":{"uri":"api\/get\/dashboard\/core\/inbox\/list\/threads","methods":["GET","HEAD"]},"api.get.db.core.inbox.search.users":{"uri":"api\/get\/dashboard\/core\/inbox\/search\/users","methods":["GET","HEAD"]},"api.get.db.core.activity.route.list":{"uri":"api\/get\/dashboard\/core\/activity\/route\/list","methods":["GET","HEAD"]},"api.get.db.core.user_menu":{"uri":"api\/get\/dashboard\/core\/user\/menu","methods":["GET","HEAD"]},"api.get.db.common.ddl.list.countries":{"uri":"api\/get\/dashboard\/common\/ddl\/list\/countries","methods":["GET","HEAD"]},"api.get.db.common.ddl.list.statuses":{"uri":"api\/get\/dashboard\/common\/ddl\/list\/statuses","methods":["GET","HEAD"]},"api.get.db.common.tools.random.generator":{"uri":"api\/get\/dashboard\/common\/tools\/random\/generator","methods":["GET","HEAD"]},"api.post.db.company.company.save":{"uri":"api\/post\/dashboard\/company\/company\/save","methods":["POST"]},"api.post.db.company.company.edit":{"uri":"api\/post\/dashboard\/company\/company\/edit\/{id}","methods":["POST"]},"api.post.db.company.company.delete":{"uri":"api\/post\/dashboard\/company\/company\/delete\/{id}","methods":["POST"]},"api.post.db.company.branch.save":{"uri":"api\/post\/dashboard\/company\/branch\/save","methods":["POST"]},"api.post.db.company.branch.edit":{"uri":"api\/post\/dashboard\/company\/branch\/edit\/{id}","methods":["POST"]},"api.post.db.company.branch.delete":{"uri":"api\/post\/dashboard\/company\/branch\/delete\/{id}","methods":["POST"]},"api.post.db.company.warehouse.save":{"uri":"api\/post\/dashboard\/company\/warehouse\/save","methods":["POST"]},"api.post.db.company.warehouse.edit":{"uri":"api\/post\/dashboard\/company\/warehouse\/edit\/{id}","methods":["POST"]},"api.post.db.company.warehouse.delete":{"uri":"api\/post\/dashboard\/company\/warehouse\/delete\/{id}","methods":["POST"]},"api.post.db.supplier.supplier.save":{"uri":"api\/post\/dashboard\/supplier\/supplier\/save","methods":["POST"]},"api.post.db.supplier.supplier.edit":{"uri":"api\/post\/dashboard\/supplier\/supplier\/edit\/{id}","methods":["POST"]},"api.post.db.supplier.supplier.delete":{"uri":"api\/post\/dashboard\/supplier\/supplier\/delete\/{id}","methods":["POST"]},"api.post.db.product.product.save":{"uri":"api\/post\/dashboard\/product\/product\/save","methods":["POST"]},"api.post.db.product.product.edit":{"uri":"api\/post\/dashboard\/product\/product\/edit\/{id}","methods":["POST"]},"api.post.db.product.product.delete":{"uri":"api\/post\/dashboard\/product\/product\/delete\/{id}","methods":["POST"]},"api.post.db.admin.users.save":{"uri":"api\/post\/dashboard\/admin\/users\/save","methods":["POST"]},"api.post.db.admin.users.edit":{"uri":"api\/post\/dashboard\/admin\/users\/edit\/{id}","methods":["POST"]},"api.post.db.core.profile.update.profile":{"uri":"api\/post\/dashboard\/core\/profile\/update\/profile","methods":["POST"]},"api.post.db.core.profile.update.settings":{"uri":"api\/post\/dashboard\/core\/profile\/update\/settings","methods":["POST"]},"api.post.db.core.profile.update.roles":{"uri":"api\/post\/dashboard\/core\/profile\/update\/roles","methods":["POST"]},"api.post.db.core.profile.send_email_verification":{"uri":"api\/post\/dashboard\/core\/profile\/send\/verification","methods":["POST"]},"api.post.db.core.profile.change_password":{"uri":"api\/post\/dashboard\/core\/profile\/change\/password","methods":["POST"]},"api.post.db.core.inbox.save":{"uri":"api\/post\/dashboard\/core\/inbox\/save","methods":["POST"]},"api.post.db.core.inbox.edit":{"uri":"api\/post\/dashboard\/core\/inbox\/edit","methods":["POST"]},"api.post.db.core.activity.log_route":{"uri":"api\/post\/dashboard\/core\/activity\/log\/route","methods":["POST"]}}};

if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}

export { Ziggy };
