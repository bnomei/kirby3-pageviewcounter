import LastVisited from './LastVisited.vue';
import ViewCount from './ViewCount.vue';

panel.plugin('bnomei/pageviewcounter', {
  fields: { viewcount: ViewCount, lastvisited: LastVisited }
});
