window.onload = function () {
    let set = new Set(JSON.parse(localStorage.getItem('history-discovery')));
    let app = new Vue({
        el: '#app',
        data: {
            //选中的选项卡

            activeIndex: -1,
            //选项卡
            tabList: [],
            //搜索返回的结果
            searchList:[],
           /* searchList: [
                {
                    "id": 0,
                    "img": "/img/show.jpg",
                    "label": "养生",
                    "title": "这是标题",
                    "content": "这是内容",
                    "date": "2018-02-25",
                    "times": 2000,
                    "comments": 100
                },
                {
                    "id": 1,
                    "img": "/img/show.jpg",
                    "label": "养生",
                    "title": "这是标题",
                    "content": "这是内容",
                    "date": "2018-02-25",
                    "times": 2000,
                    "comments": 100
                },
                {
                    "id": 2,
                    "img": "/img/show.jpg",
                    "label": "养生",
                    "title": "这是标题",
                    "content": "这是内容",
                    "date": "2018-02-25",
                    "times": 2000,
                    "comments": 100
                }
            ],*/
            //tab页内容
            tabContent: new Map(),
            //手动改变值变化
            tabContentTracker: 0,
            //历史记录
            historyList: Array.from(set),
            //搜索的关键字
            searchKeys: '',
            //显示的页面标记
            currentPage: 1,
            //显示历史记录还是搜索内容
            isInput: true
        },
      mounted(){
          //获取 tab页内容和页面初始化数据
          this.init();

      },
        methods: {
          //获取推荐数据
            getRecommendList:function () {
                let self = this;
                self.activeIndex = -1,
                $.post('/api/found/recommend', {
                    page: self.currentPage
                }, function (data) {
                    self.searchList = data.data;
                });
            },
            init: function () {
                let self = this;
                $.getJSON('/api/found/category',  function (data) {
                    self.tabList =  data.data;
                });
            },
            loadTabContent: function (tabId, index) {
                this.activeIndex = index;
                this.getItemList(tabId);
            },
            //列表数据
            getItemList: function (tabId) {
                let self = this;
                if (self.tabContent.get(tabId)) {
                    return self.tabContent.get(tabId);
                } else {
                    //请求获取数据
                    $.post('/api/found/article', {
                        type_id: tabId,
                        page: self.currentPage,
                    }, function (data) {
                        console.log('推荐文章内容',data)
                        self.searchList = data.data;
                        /*self.tabContent.set(tabId, data);
                        self.tabContentTracker += 1;
                        return self.tabContent.get(tabId);*/
                    });
                }
            },
            showSearch: function () {
                //显示搜索页面
                this.currentPage = 2;
                setTimeout(function () {
                    document.getElementById('search-keys').focus();
                }, 100);
            },
            //返回首页
            goBack: function () {
                this.isInput = false;
                this.currentPage = 1;
                this.searchKeys = '';
            },
            //清除历史记录
            clearHistory: function () {
                localStorage.removeItem('history-discovery');
                app.historyList = JSON.parse(localStorage.getItem('history-discovery'));
                set.clear();
            },
            //立即搜索
            searchContent: function (content) {
                if (content) {
                    this.searchKeys = content;
                }
                //改变显示状态
                this.isInput = false;
                if (this.searchKeys.trim() != '') {
                    set.add(this.searchKeys);
                    localStorage.setItem('history-discovery', JSON.stringify(Array.from(set)));
                    app.historyList = Array.from(set);
                }
            },
            goInner: function (id) {
                mui.openWindow({
                    url: 'discovery-detail.html'
                })
            },
            //显示历史记录
            showHistory: function () {
                //改变显示状态
                this.isInput = true;
            },



        },
        created: function () {
            //获取推荐数据
             this.getRecommendList();
        }
    });
    /**
     * 固定tab
     */
        //获取 id="course_container" 元素，offsetTop是当前元素·距离网页窗口顶部的距离
    let offset_top = document.getElementById("tab-container").offsetTop;
    let isSetHeight = false;
    $(window).scroll(function () {
        //获取垂直滚动的距离（scrollTop()是从顶部开始滚动产生的距离）
        let scroll_top = $(document).scrollTop();
        //防止重复设置高度页面抖动
        if (scroll_top > offset_top) {
            // 到达顶部位置，动态的添加元素属性，并给元素添加相应的元素样式
            document.getElementById("tab-container").classList.add("fixed");
        }
        else {
            // 同理，把之前添加的元素移除即可
            document.getElementById("tab-container").classList.remove("fixed");
        }
    });

}