一个从第一版主采集小说的爬虫 嘿嘿嘿

2020.7.30

以前第一版主的文字图片命名较为规律，所以采取的方式想看那本书就用爬虫去采集那本的策略。最近网站更新了策略，文字图片命名没有规律了，继续这样搞的话就很吃力，所以转化策略，想把整个网站的网页都扒下来在再慢慢解析，目前扫描出来共用30多万章节数据，因为php只能单线程，所以爬的很慢，爬虫跑了3天目前只爬取了9万条数据下来。

说说这次的爬取思路：
    第一版主采用文字中插入图片的方法来防止爬虫爬取，而且会定期更改图片资源的引用链接。所有这次我的大致思路是先把所有的网页爬下来存到数据库中，然后对这些数据进行扫描抓出图片，再把图片资源爬取下来，最后手动识别图片资源中的文字(因为图片资源用的是矢量图,没法用ocr提取文字，这点还是比较烦的),最后再对资源扫描，根据上一步弄好的图片字典,替换掉图片，这样就完成了解析。

具体流程
    1.扫描书籍信息
    先从网站中抓取到所有书籍的信息
    2.扫描书籍章节信息
    根据上一步抓取的书籍信息，抓取书籍的章节信息
    3.抓取章节内容
    根据上一步抓取的章节信息，进一步抓取到章节内容
    4.扫描图片
    扫描出章节内容中图片资源，并把图片资源抓取到本地
    5.手动识别图片文字
    根据抓取下来的图片资源，识别文字
    6.生成标准章节
    再次扫描章节内容，替换图片，生成章节
    7.访问本地对应链接，导出书籍txt

后续想法
    目前都是通过命令行操作的，感觉还是比较繁琐。手动识别文字也比较难受，所有后续的想法是弄一个可视化管理后台，方便操作，作重要的是方便处理图片文字的识别，需要解决的技术难点是如何保证网页调起命令行，以及脚本运行进度的可视化，脚本的稳定性，多线程爬虫的实现，这些都是下个版本需要考虑的。
